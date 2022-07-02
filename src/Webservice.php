<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Exceptions\InvalidLoginException;
use Fundevogel\Pcbis\Exceptions\NoRecordFoundException;
use Fundevogel\Pcbis\Exceptions\OfflineModeException;
use Fundevogel\Pcbis\Exceptions\UnknownTypeException;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Products\Product;
use Fundevogel\Pcbis\Products\Books\Types\Ebook;
use Fundevogel\Pcbis\Products\Books\Types\Hardcover;
use Fundevogel\Pcbis\Products\Books\Types\Schoolbook;
use Fundevogel\Pcbis\Products\Books\Types\Softcover;
use Fundevogel\Pcbis\Products\Media\Types\Audiobook;
use Fundevogel\Pcbis\Products\Media\Types\Movie;
use Fundevogel\Pcbis\Products\Media\Types\Music;
use Fundevogel\Pcbis\Products\Media\Types\Sound;
use Fundevogel\Pcbis\Products\Nonbook\Types\Boardgame;
use Fundevogel\Pcbis\Products\Nonbook\Types\Calendar;
use Fundevogel\Pcbis\Products\Nonbook\Types\Map;
use Fundevogel\Pcbis\Products\Nonbook\Types\Nonbook;
use Fundevogel\Pcbis\Products\Nonbook\Types\Notes;
use Fundevogel\Pcbis\Products\Nonbook\Types\Software;
use Fundevogel\Pcbis\Products\Nonbook\Types\Stationery;
use Fundevogel\Pcbis\Products\Nonbook\Types\Toy;
use Fundevogel\Pcbis\Products\Nonbook\Types\Videogame;

use SoapClient;
use SoapFault;

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
    private ?string $sessionID = null;


    /**
     * SOAP client used when connecting to KNV's API
     *
     * @var \SoapClient
     */
    private SoapClient $client;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @param string $cache Cache object
     * @return void
     */
    public function __construct(?array $credentials = null, public mixed $cache = null)
    {
        # If credentials not specified ..
        if (is_null($credentials)) {
            # .. activate offline mode
            $this->offlineMode = true;
        } else {
            # Attempt to ..
            try {
                # .. fire up SOAP client
                $this->client = new SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
                    'soap_version' => SOAP_1_2,
                    'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                    'cache_wsdl' => WSDL_CACHE_BOTH,
                    'trace' => true,
                    'exceptions' => true,
                ]);

                # Authenticate with API
                $this->logIn($credentials);

                # If network errors out ..
            } catch (SoapFault $e) {
                # .. activate offline mode
                $this->offlineMode = true;
            }
        }
    }


    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        if (!is_null($this->sessionID)) {
            $this->logOut();
        }
    }


    /**
     * Methods
     */

    /**
     * Authenticates with KNV's API & generates session token
     *
     * @param array $credentials Login credentials
     * @throws \Fundevogel\Pcbis\Exceptions\InvalidLoginException
     * @return void
     */
    private function logIn(array $credentials): void
    {
        try {
            # Log in & aquire session token
            $this->sessionID = $this->client->WSCall(['LoginInfo' => $credentials])->SessionID;
        } catch (SoapFault $e) {
            # If 'login error' code is present ..
            if ($e?->detail?->TLDFehler?->errcode == '20000') {
                # .. report back ..
                throw new InvalidLoginException($e->getMessage());
            }

            # .. otherwise, invoke offline mode
            throw $e;
        }
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
     * @throws \Fundevogel\Pcbis\Exceptions\OfflineModeException
     * @return array
     */
    private function query(string $identifier): array
    {
        if ($this->offlineMode) {
            throw new OfflineModeException('Offline mode enabled, API calls are not allowed.');
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
        $value = null;

        if (!is_null($this->cache)) {
            # If specified ..
            if ($forceRefresh) {
                # .. clear cache beforehand
                $this->cache->delete($identifier);
            }

            # Retrieve from cache
            $value = $this->cache->get($identifier, function() use($identifier) {
                return $this->query($identifier);
            });
        }

        if (is_null($value)) {
            $value = $this->query($identifier);
        }

        return $value;
    }


    /**
     * Instantiates 'Product' object from single EAN/ISBN
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     * @throws \Fundevogel\Pcbis\Exceptions\UnknownTypeException
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public function load(string $identifier, bool $forceRefresh = false): Product
    {
        # Define available product types
        $types = [
            # (1) Books
            'AG' => 'ePublikation',
            'HC' => 'Hardcover',
            'SB' => 'Schulbuch',
            'TB' => 'Taschenbuch',

            # (2) Media
            'AC' => 'Hörbuch',
            'AD' => 'Film',
            'AF' => 'Tonträger',
            'AK' => 'Musik',

            # (3) Nonbook
            'AB' => 'Nonbook',
            'AE' => 'Software',
            'AH' => 'Games',
            'AI' => 'Kalender',
            'AJ' => 'Landkarte/Globus',
            'AL' => 'Noten',
            'AM' => 'Papeterie/PBS',
            'AN' => 'Spiel',
            'AO' => 'Spielzeug',
        ];

        # Fetch raw data
        $data = $this->fetch($identifier, $forceRefresh);

        # Determine type identifier
        $code = $data['Sortimentskennzeichen'];

        # If product type is unknown ..
        if (!array_key_exists($code, $types)) {
            # .. fail early
            throw new UnknownTypeException(sprintf('Unknown type identifier: "%s"', $code));
        }

        # Create instance based on product type
        $type = $types[$code];

        switch ($type) {
            # Books
            case 'ePublikation':
                return new Ebook($data, $this);
            case 'Hardcover':
                return new Hardcover($data, $this);
            case 'Schulbuch':
                return new Schoolbook($data, $this);
            case 'Taschenbuch':
                return new Softcover($data, $this);

            # Media
            case 'Film':
                return new Movie($data, $this);
            case 'Hörbuch':
                return new Audiobook($data, $this);
            case 'Musik':
                return new Music($data, $this);
            case 'Tonträger':
                return new Sound($data, $this);

            # Nonbook
            case 'Games':
                return new Videogame($data, $this);
            case 'Kalender':
                return new Calendar($data, $this);
            case 'Landkarte/Globus':
                return new Map($data, $this);
            case 'Nonbook':
                return new Nonbook($data, $this);
            case 'Noten':
                return new Notes($data, $this);
            case 'Papeterie/PBS':
                return new Stationery($data, $this);
            case 'Software':
                return new Software($data, $this);
            case 'Spiel':
                return new Boardgame($data, $this);
            case 'Spielzeug':
                return new Toy($data, $this);
        }
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
