<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api;

use Fundevogel\Pcbis\Api\Exceptions\Factory;
use Fundevogel\Pcbis\Exceptions\OfflineModeException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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
        throw Factory::create(json_decode((string) $response->getBody()));
    }


    /**
     * Authenticates with KNV's API
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
     * @return \stdClass Response body as JSON object
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
     * Checks product availability via OLA ('Online Lieferbarkeits-Abfrage')
     *
     * @param string $identifier Product EAN/ISBN
     * @param int $quantity Number of products to be delivered
     * @param string $type OLA type (either 'anfrage', 'bestellung' or 'storno')
     * @return \stdClass Response body as JSON object
     */
    public function ola(string $identifier, int $quantity = 1, string $type = 'anfrage'): \stdClass
    {
        # Determine OLA items
        # TODO: Make it fully configurable
        $query = [
            'olaItems' => [
                'bestellNummer' => ['ean' => $identifier],
                'menge' => $quantity,
            ],
        ];

        return $this->call(sprintf('ola/%s', $type), $query);
    }
}
