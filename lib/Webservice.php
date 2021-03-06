<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis;

use Pcbis\Exceptions\IncompatibleClientException;
use Pcbis\Exceptions\InvalidISBNException;
use Pcbis\Exceptions\InvalidLoginException;
use Pcbis\Exceptions\NoRecordFoundException;

use Pcbis\Helpers\Butler;
use Pcbis\Api\Ola;

use Pcbis\Products\Factory;
use Pcbis\Products\Books\Books;

use Biblys\Isbn\Isbn;
use Doctrine\Common\Cache\FilesystemCache;

use SoapClient;
use SoapFault;


/**
 * Class Webservice
 *
 * Retrieves information from KNV's API & caches the resulting data
 *
 * @package PHPCBIS
 */

class Webservice
{
    /**
     * Current version number of PHPCBIS
     */
    const VERSION = '2.0.2';


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
    private $sessionID = null;


    /**
     * SOAP client used when connecting to KNV's API
     *
     * @var \SoapClient
     */
    private $client = null;


    /**
     * Cache object storing product data fetched from KNV's API
     *
     * @var \Doctrine\Common\Cache\FilesystemCache
     */
    private $cache = null;


    /**
     * Whether cached data should be refreshed
     *
     * @var bool
     */
    private $forceRefresh;


    /**
     * Translatable strings
     *
     * @var array
     */
    private $translations = [];


    /**
     * Constructor
     */

    public function __construct(array $credentials = null, string $cachePath = './.cache')
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
        } catch (SoapFault $e) {
            # Activate offline mode on network error
            $this->offlineMode = true;
        }

        if (!$this->offlineMode) {
            # Check compatibility
            if ($this->client->CheckVersion('2.0') === '2') {
                throw new IncompatibleClientException('Your client is outdated, please update to newer version.');
            }

            # Initialize API driver
            if ($credentials !== null) {
                $this->sessionID = $this->logIn($credentials);
            }
        }

        # Initialize cache
        $this->cache = new FilesystemCache($cachePath);
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
     * Setters & getters
     */

    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }


    public function getTranslations(): array
    {
        return $this->translations;
    }


    /**
     * Methods
     */

    /**
     * Uses credentials to log into KNV's API & generates a sessionID
     *
     * @param array $credentials
     * @throws \PHPCBIS\Exceptions\InvalidLoginException
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
     * @param string $isbn
     * @throws \PHPCBIS\Exceptions\InvalidLoginException
     * @return array
     */
    private function query(string $isbn)
    {
        if ($this->offlineMode) {
            throw new InvalidLoginException('Offline mode enabled, API calls are not allowed.');
        }

        # For getting started with KNV's (surprisingly well documented) german API,
        # see http://www.knv-zeitfracht.de/wp-content/uploads/2020/07/Webservice_2.0.pdf
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
                        'Suchwert' => $isbn,
                        'Schwert2' => '',
                        'Suchart' => 'Genau',
                    ],
                ],
            ],
            # Read results of the query & return first result
            'Lesen' => [
                'SatzVon' => 1,
                'SatzBis' => 1,
                'Format' => 'KNVXMLLangText',
            ],
        ]);

        if ($query->Suchergebnis->TrefferGesamt > 0) {
            $result = $query->Daten->Datensaetze->Record->ArtikelDaten;
            $array = Butler::loadXML($result);

            return Butler::last($array);
        }

        throw new NoRecordFoundException('No database record found for ISBN ' . $isbn);
    }


    /**
     * Fetches information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @param bool $forceRefresh - Whether to update cached data
     * @return array
     */
    private function fetch(string $isbn, bool $forceRefresh): array
    {
        if ($this->cache->contains($isbn) && $forceRefresh) {
            $this->cache->delete($isbn);
        }

        # Data might be cached already ..
        $fromCache = true;

        if (!$this->cache->contains($isbn)) {
            $result = $this->query($isbn);
            $this->cache->save($isbn, $result);

            # .. turns out, it was not
            $fromCache = false;
        }

        return [
            'fromCache' => $fromCache,
            'source'    => $this->cache->fetch($isbn),
        ];
    }


    /**
     * Validates and formats given EAN/ISBN
     * For more information, see https://github.com/biblys/isbn
     *
     * @param string $isbn - International Standard Book Number
     * @throws \Pcbis\Exceptions\InvalidISBNException
     * @return string
     */
    private function validate(string $isbn): string
    {
        if (Butler::length($isbn) === 13 && (Butler::startsWith($isbn, '4') || Butler::startsWith($isbn, '5'))) {
            # Most likely non-convertable EAN
            return $isbn;
        }

        try {
            $isbn = Isbn::convertToIsbn13($isbn);
        } catch(\Exception $e) {
            throw new InvalidISBNException($e->getMessage());
        }

        return $isbn;
    }


    /**
     * Checks if product is available for delivery via OLA query
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @param int $quantity - Number of products to be delivered
     * @return \Pcbis\Api\Ola
     */
    public function ola(string $isbn, int $quantity = 1): \Pcbis\Api\Ola
    {
        $id = 'ola-' . $isbn;

        # Check cache for OLA request
        if (!$this->cache->contains($id)) {
            $result = $this->client->WSCall([
                # Log in using sessionID
                'SessionID' => $this->sessionID,
                'OLA' => [
                    'Art' => 'Abfrage',
                    'OLAItem' => [
                        'Bestellnummer' => [
                            'ISBN' => $isbn,
                        ],
                        'Menge' => $quantity,
                    ],
                ],
            ]);

            # Store result for an hour
            $this->cache->save($id, $result, 3600);
        }

        $ola = $this->cache->fetch($id);

        return new Ola($ola->OLAResponse->OLAResponseRecord);
    }


    /**
     * Instantiates `Product` object from single EAN/ISBN
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @param bool $forceRefresh - Whether to update cached data
     * @return \Pcbis\Products\Product
     */
    public function load(string $isbn, bool $forceRefresh = false): \Pcbis\Products\Product
    {
        $isbn = $this->validate($isbn);
        $data = $this->fetch($isbn, $forceRefresh);

        $props = [
            'api'          => $this,
            'isbn'         => $isbn,
            'fromCache'    => $data['fromCache'],
            'translations' => $this->translations,
        ];

        return Factory::factory($data['source'], $props);
    }


    /**
     * Instantiates `Books` object from multiple EANs/ISBNs
     *
     * TODO: This needs to be re-evaluated / outsourced to a factory
     *
     * @param array $isbns - A group of books' ISBNs
     * @return \Pcbis\Products\Books\Books
     */
    public function loadBooks(array $isbns): \Pcbis\Products\Books\Books
    {
        $books = [];

        foreach ($isbns as $isbn) {
            try {
                $book = $this->load($isbn);

                if ($book->isBook() || $book->isAudiobook()) {
                    $books[] = $book;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return new Books($books);
    }
}
