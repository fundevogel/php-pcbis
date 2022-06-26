<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 3.0.0-beta.1
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Butler;
use Fundevogel\Pcbis\Exceptions\IncompatibleClientException;
use Fundevogel\Pcbis\Exceptions\InvalidLoginException;
use Fundevogel\Pcbis\Exceptions\NoRecordFoundException;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Products\Factory;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use Exception;
use SoapClient;
use SoapFault;
use stdClass;


/**
 * Class Webservice
 *
 * Retrieves information from KNV's API & caches the resulting data
 */
class Webservice
{
    /**
     * Properties
     */

    /**
     * Whether to work offline (cached books only)
     *
     * @var bool
     */
    private $offlineMode = false;


    /**
     * Session identifier retrieved when first connecting to KNV's API
     *
     * @var string
     */
    private $sessionID;


    /**
     * SOAP client used when connecting to KNV's API
     *
     * @var \SoapClient
     */
    private $client;


    /**
     * Cache storing data fetched from KNV's API
     *
     * @var \Symfony\Component\Cache\Adapter\FilesystemAdapter
     */
    private $cache;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @param string $cachePath Cache directory
     * @param int $ttl Lifetime for cache items (in seconds)
     */
    public function __construct(?array $credentials = null, string $cachePath = './.cache', int $ttl = 0)
    {
        try {
            # Fire up SOAP client
            $this->client = new SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
                'soap_version' => SOAP_1_2,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                'cache_wsdl' => WSDL_CACHE_BOTH,
                'trace' => true,
                'exceptions' => true,
            ]);

            # Check API compatibility
            if ($this->client->CheckVersion('2.0') === '2') {
                throw new IncompatibleClientException('Your client is outdated, please update to newer version.');
            }

            # Authenticate with API (if necessary)
            if (!is_null($credentials)) {
                $this->sessionID = $this->logIn($credentials);
            }

        } catch (SoapFault $e) {
            # Activate offline mode on network error
            $this->offlineMode = true;
        }

        # Initialize cache
        $this->cache = new FilesystemAdapter('pcbis', $ttl, $cachePath);
    }


    /**
     * Destructor
     */
    public function __destruct()
    {
        if (!$this->offlineMode) {
            $this->logOut();
        }
    }


    /**
     * Methods
     */

    /**
     * Uses credentials to log into KNV's API & generates a sessionID
     *
     * @param array $credentials Login credentials
     * @throws \Fundevogel\Pcbis\Exceptions\InvalidLoginException
     * @return string
     */
    private function logIn(array $credentials): string
    {
        try {
            $query = $this->client->WSCall(['LoginInfo' => $credentials]);

        } catch (SoapFault $e) {
            throw new InvalidLoginException($e->getMessage());
        }

        return $query->SessionID;
    }


    /**
     * Uses sessionID to log out of KNV's API
     *
     * @return void
     */
    private function logOut(): void
    {
        $this->client->WSCall([
            'SessionID' => $this->sessionID,
            'Logout' => true,
        ]);
    }


    /**
     * Fetches raw product data from KNV
     *
     * .. if product for given EAN/ISBN exists
     *
     * @param string $identifier Product EAN/ISBN
     * @throws \Fundevogel\Pcbis\Exceptions\InvalidLoginException
     * @return array
     */
    private function query(string $identifier)
    {
        if ($this->offlineMode) {
            throw new InvalidLoginException('Offline mode enabled, API calls are not allowed.');
        }

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
     * Fetches information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     * @return array
     */
    public function fetch(string $identifier, bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            $this->cache->delete($identifier);
        }

        return $this->cache->get($identifier, function (ItemInterface $item) use ($identifier): array
        {
            return $this->query($identifier);
        });
    }


    /**
     * Instantiates `Product` object from single EAN/ISBN
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     *
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public function load(string $identifier, bool $forceRefresh = false)
    {
        # Fetch raw data for given ISBN
        $data = $this->fetch($identifier, $forceRefresh);

        return Factory::factory($data, ['api' => $this, 'identifier' => $identifier]);
    }


    /**
     * Checks if product is available for delivery via OLA query
     *
     * @param string $identifier Product EAN/ISBN
     * @param int $quantity Number of products to be delivered
     */
    public function ola(string $identifier, int $quantity = 1): stdClass
    {
        /**
         * @var stdClass
         */
        return new Ola($this->cache->get('ola-' . $identifier, function (ItemInterface $item) use ($identifier, $quantity): stdClass
        {
            # Expire after one hour
            $item->expiresAfter(3600);

            return $this->client->WSCall([
                # Log in using sessionID
                'SessionID' => $this->sessionID,
                'OLA' => [
                    'Art' => 'Abfrage',
                    'OLAItem' => [
                        'Bestellnummer' => ['ISBN' => $identifier],
                        'Menge' => $quantity,
                    ],
                ],
            ]);
        }));
    }
}
