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
use Fundevogel\Pcbis\Api\Exceptions\Exception;
use Fundevogel\Pcbis\Exceptions\OfflineModeException;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use stdClass;

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
     * @throws \Fundevogel\Pcbis\Exceptions\OfflineModeException|\Fundevogel\Pcbis\Api\Exceptions\Exception No API calls when offline
     * @return \stdClass Response body as JSON object
     */
    private function call(string $resource, array $data, string $type = 'POST'): stdClass
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

        /**
         * @var \Fundevogel\Pcbis\Api\Exceptions\Exception
         */
        $exception = Factory::create(json_decode((string) $response->getBody()));

        # .. otherwise everything goes south
        throw $exception;
    }


    /**
     * Authenticates with KNV's API
     *
     * @param array $credentials Login credentials
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
     * @param array|string $query Query data
     * @return mixed Response body as JSON object
     */
    public function suche(array|string $query): stdClass
    {
        # If it resembles product EAN/ISBN ..
        if (is_string($query)) {
            # .. build simple query data from it
            $query = [
                'suche' => [
                    # Search across all databases
                    'datenbanken' => ['ZF', 'ZFBG'],
                    'zfSuche' => $query,
                ],
                # Read results of the query & return first result
                'lesen' => [
                    'satzVon' => 1,
                    'satzBis' => 1,
                    'satzFormat' => 'LANGTEXT',
                ],
            ];
        }

        # Send query & report back
        return $this->call('suche', $query);
    }


    /**
     * Provides predefined filters
     *
     * @param array $query Query data
     * @param string $groupID Filter group identifier
     * @return \stdClass Response body as JSON object
     */
    public function filter(array $query, ?string $groupID = null): stdClass
    {
        $type = is_null($groupID)
            ? 'filter'
            : sprintf('filter/%s', $groupID)
        ;

        return $this->call($type, $query, 'GET');
    }


    /**
     * Provides matching search terms for specific field entry
     *
     * @param array $query Query data
     * @return \stdClass Response body as JSON object
     */
    public function register(array $query): stdClass
    {
        return $this->call('register', $query);
    }


    /**
     * Checks product availability via OLA ('Online Lieferbarkeits-Abfrage')
     *
     * @param array|string $query Query data
     * @param int $quantity Number of products to be delivered
     * @param string $type OLA type (either 'anfrage', 'bestellung' or 'storno')
     * @return \stdClass Response body as JSON object
     */
    public function ola(array|string $query, int $quantity = 1, string $type = 'anfrage'): stdClass
    {
        # If it resembles product EAN/ISBN ..
        if (is_string($query)) {
            # .. build simple query data from it
            $query = [
                'olaItems' => [
                    'bestellNummer' => ['ean' => $query],
                    'menge' => $quantity,
                ],
            ];
        }

        return $this->call(sprintf('ola/%s', $type), $query);
    }


    /**
     * Retrieves download link for eBooks
     *
     * @param array|string $query Query data
     * @return \stdClass Response body as JSON object
     */
    public function ebook(array|string $query): stdClass
    {
        # If it resembles product EAN/ISBN ..
        if (is_string($query)) {
            # .. build simple query data from it
            $query = ['ebookItems' => ['ean' => $query]];
        }

        return $this->call('ebook', $query);
    }


    /**
     * Provides matching search terms for any field entry
     *
     * @param array|string $query Query data
     * @return \stdClass Response body as JSON object
     */
    public function suchvorschlaege(array|string $query): stdClass
    {
        # If it resembles product EAN/ISBN ..
        if (is_string($query)) {
            # .. build simple query data from it
            $query = ['teilSuchwert' => $query];
        }

        return $this->call('suchvorschlaege', $query);
    }


    /**
     * Retrieves information about CMP ('Category Management Pakete')
     *
     * @param array $query Query data
     * @return \stdClass Response body as JSON object
     */
    public function cmpaket(array $query): stdClass
    {
        return $this->call('cmpaket', $query, 'GET');
    }
}
