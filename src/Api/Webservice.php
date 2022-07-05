<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Exceptions\InvalidLoginException;
use Fundevogel\Pcbis\Exceptions\NoRecordFoundException;
use Fundevogel\Pcbis\Exceptions\OfflineModeException;
use Fundevogel\Pcbis\Exceptions\UnknownTypeException;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Utilities\Butler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

/**
 * Class Webservice
 *
 * Authenticates with & retrieves data from KNV's API
 */
final class Webservice
{
    /**
     * Properties
     */

    /**
     * HTTP client used for connecting to KNV's API
     *
     * @var \GuzzleHttp\Client
     */
    private Client $client;


    /**
     * HTTP request headers
     *
     * @var array
     */
    public $headers = [];


    /**
     * Base URL
     *
     * @var string
     */
    public $url = 'https://wstest.pcbis.de/ws30';


    /**
     * Whether to work offline (cached books only)
     *
     * @var bool
     */
    private $offlineMode = false;


    /**
     * Token retrieved when first connecting to KNV's API
     *
     * @var string
     */
    public ?string $token = null;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @return void
     */
    public function __construct(?array $credentials = null)
    {
        # If credentials passed ..
        if (is_array($credentials)) {
            # (1) .. fire up HTTP client
            $this->client = new Client();

            # (2) .. authenticate with API
            $this->login($credentials);
        }

        # If token not set ..
        if (is_null($this->token)) {
            # .. activate offline mode
            $this->offlineMode = true;
        }

        # Note: Since offline mode is only useful when data has been cached before and
        # there's no way determining what to do in case of a 'ClientException', we let
        # developers handle any client-side error thrown along the way
    }


    /**
     * Methods
     */


    /**
     * Makes API calls
     *
     * @param string $resource API resource being called
     * @param array $data Data being sent as JSON object
     * @param string $type Request type (mostly 'POST')
     * @throws \Exception|\Fundevogel\Pcbis\Exceptions\OfflineModeException
     * @return \stdClass Response body as JSON object
     */
    private function call(string $resource, array $data, string $type = 'POST'): \stdClass
    {
        if ($this->offlineMode) {
            throw new OfflineModeException('Offline mode enabled, API calls are not allowed.');
        }

        # Determine target URL
        $url = sprintf('%s/%s', $this->url, $resource);

        # Define payload
        $payload = ['json' => $data, 'headers' => $this->headers];

        if (is_string($this->token)) {
            $payload['headers'] = ['Authorization' => sprintf('Bearer %s', $this->token)];
        }

        # Make the call
        $response = $this->client->request($type, $url, $payload);

        # If response checks out ..
        if ($response->getStatusCode() == 200) {
            # .. report back
            return json_decode((string) $response->getBody());
        }

        # .. otherwise everything goes south
        throw new \Exception((string) $response->getBody());
    }


    /**
     * Authenticates with KNV's API & generates session token
     *
     * @param array $credentials Login credentials
     * @throws \Fundevogel\Pcbis\Exceptions\KNVException
     * @return bool
     */
    public function login(array $credentials): bool
    {
        # If API call succeeds ..
        if ($response = $this->call('login', $credentials)) {
            # .. receive token
            $this->token = $response?->token;

            # .. report back
            return true;
        }

        # TODO: Exception handling?
        return false;
    }


    /**
     * Fetches raw product data from KNV's API
     *
     * @param string $identifier Product EAN/ISBN
     * @return \stdClass
     */
    public function suche(string $identifier): \stdClass
    {
        # Determine search parameters
        # TODO: Make it fully configurable
        $query = [
            'suche' => [
                # Search across all databases
                'datenbanken' => ['ZF', 'ZFBG'],
                'zfSuche' => $identifier,
            ],
            # Read results of the query & return first result
            'lesen' => [
                'satzVon' => 1,
                'satzBis' => 1,
                'satzFormat' => 'LANGTEXT',
            ],
        ];

        # Send query & report back
        return $this->call('suche', $query);
    }


    /**
     *
     * .. if product for given EAN/ISBN exists
     *
     * @param string $identifier Product EAN/ISBN
     * @throws \Fundevogel\Pcbis\Exceptions\OfflineModeException
     * @return array
     */
    private function query(string $identifier): array
    {
        # For getting started with KNV's (surprisingly well documented) german API,
        # see https://zeitfracht-medien.de/wp-content/uploads/2022/05/ZF-Webservice_3.0-1.pdf
        $query = $this->client->WSCall([
            # Log in using sessionID
            'SessionID' => $this->sessionID,

            # Start new database query
            'Suchen' => [
                # Search across all databases
                'Datenbank' => [
                    'KNV',
                    'KNVBG',
                    'BakerTaylor',
                    'Gardners',
                ],
                'Suche' => [
                    'SimpleTerm' => [
                        # Simple search suffices for querying single ISBN
                        'Suchfeld' => 'ISBN',
                        'Suchwert' => $identifier,
                        'Schwert2' => '',
                        'Suchart'  => 'Genau',
                    ],
                ],
            ],
            # Read results of the query & return first result
            'Lesen' => [
                'SatzVon' => 1,
                'SatzBis' => 1,
                'Format'  => 'KNVXMLLangText',
            ],
        ]);

        if ($query->Suchergebnis->TrefferGesamt > 0) {
            $result = $query->Daten->Datensaetze->Record->ArtikelDaten;
            $array = Butler::loadXML($result);

            return A::last($array);
        }

        throw new NoRecordFoundException(sprintf('No database record found for "%s".', $identifier));
    }


    /**
     * Checks if product is available for delivery via OLA query
     *
     * @param string $identifier Product EAN/ISBN
     * @param int $quantity Number of products to be delivered
     * @throws \Fundevogel\Pcbis\Exceptions\OfflineModeException
     * @return \Fundevogel\Pcbis\Api\Ola
     */
    public function ola(string $identifier, int $quantity = 1): Ola
    {
        if ($this->offlineMode) {
            throw new OfflineModeException('Offline mode enabled, API calls are not allowed.');
        }

        return new Ola($this->client->WSCall([
            # Log in using sessionID
            'SessionID' => $this->sessionID,
            'OLA' => [
                'Art' => 'Abfrage',
                'OLAItem' => [
                    'Bestellnummer' => ['ISBN' => $identifier],
                    'Menge' => $quantity,
                ],
            ],
        ]));
    }
}
