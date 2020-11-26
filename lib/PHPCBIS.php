<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/pcbis2pdf
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace PHPCBIS;

use PHPCBIS\Exceptions\IncompatibleClientException;
use PHPCBIS\Exceptions\InvalidLoginException;
use PHPCBIS\Exceptions\InvalidISBNException;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\KNV\OLA;

use PHPCBIS\Products\Factory;
use PHPCBIS\Products\Books\Books;

use Biblys\Isbn\Isbn as ISBN;
use Doctrine\Common\Cache\FilesystemCache as FileCache;

use Exception;
use SoapClient;
use SoapFault;


/**
 * Class PHPCBIS
 *
 * Retrieves information from KNV's API & caches the resulting data
 *
 * @package PHPCBIS
 */

class PHPCBIS
{
    /**
     * Current version number of PHPCBIS
     */
    const VERSION = '2.0.0-alpha.4';


    /**
     * Path to cached book information received from KNV's API
     *
     * @var string
     */
    private $cachePath = './.cache';


    /**
     * SOAP client used when connecting to KNV's API
     *
     * @var \SoapClient
     */
    private $client = null;


    /**
     * Session identifier retrieved when first connecting to KNV's API
     *
     * @var string
     */
    private $sessionID;


    /**
     * Translatable strings
     *
     * @var array
     */
    private $translations = [];


    /**
     * Constructor
     */

    public function __construct(array $credentials = null)
    {
        # Fire up SOAP client
        $this->client = new SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
            'soap_version' => SOAP_1_2,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'trace' => true,
            'exceptions' => true,
        ]);

        # Check compatibility
        if (!$this->isCompatible()) {
            throw new IncompatibleClientException('Your client is outdated, please update to newer version.');
        }

        # Insert credentials for KNV's API
        if ($credentials === null) {
            throw new InvalidLoginException('Please provide valid login credentials.');
        }

        # Log in & store sessionID
        $this->sessionID = $this->logIn($credentials);
    }


    /**
     * Destructor
     */

    public function __destruct()
    {
        $this->logOut();
    }


    /**
     * Setters & getters
     */

    public function setCachePath(string $cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function getCachePath()
    {
        return $this->cachePath;
    }

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
     * Validates and formats given EAN/ISBN
     * For more information, see https://github.com/biblys/isbn
     *
     * @param string $isbn - International Standard Book Number
     * @throws \PHPCBIS\Exceptions\InvalidISBNException
     * @return string
     */
    private function validateISBN(string $isbn): string
    {
        if (Butler::length($isbn) === 13 && (Butler::startsWith($isbn, '4') || Butler::startsWith($isbn, '5'))) {
            # Most likely non-convertable EAN
            return $isbn;
        }

        $isbn = new ISBN($isbn);

        try {
            $isbn->validate();
            $isbn = $isbn->format('ISBN-13');
        } catch(Exception $e) {
            throw new InvalidISBNException($e->getMessage());
        }

        return $isbn;
    }


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
            $query = $this->client->WSCall([
                'LoginInfo' => $credentials,
            ]);
        } catch (SoapFault $e) {
            throw new InvalidLoginException($e->getMessage());
        }

        return $query->SessionID;
    }


    /**
     * Checks compatibility of PHPCBIS & KNV's API
     *
     * @return bool
     */
    private function isCompatible(): bool
    {
        return $this->client->CheckVersion('2.0') !== '2';
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
     * Fetches raw book data from KNV
     *
     * .. if book for given ISBN exists
     *
     * @param string $isbn
     * @return array
     */
    private function fetchData(string $isbn)
    {
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

        return [];
    }


    /**
     * Checks if book is available for delivery via OLA query
     *
     * @param string $isbn - A given book's ISBN
     * @param int $quantity - Number of books to be delivered
     * @return \PHPCBIS\KNV\OLA
     */
    public function ola(string $isbn, int $quantity = 1)
    {
        $isbn = $this->validateISBN($isbn);

        $query = $this->client->WSCall([
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

        return new OLA($query->OLAResponse->OLAResponseRecord);
    }


    /**
     * Fetches book information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $isbn - A given book's ISBN
     * @return array
     */
    private function fetchBook(string $isbn): array
    {
        $driver = new FileCache($this->cachePath);
        $fromCache = $driver->contains($isbn);

        if (!$fromCache) {
            $result = $this->fetchData($isbn);
            $driver->save($isbn, $result);
        }

        return [
            'fromCache' => $fromCache,
            'source'    => $driver->fetch($isbn),
        ];
    }


    /**
     * Validates ISBN & builds `Book` object
     *
     * @param string $isbn - A given book's ISBN
     * @return \PHPCBIS\Products\Product
     */
    public function loadBook(string $isbn): \PHPCBIS\Products\Product
    {
        $isbn = $this->validateISBN($isbn);
        $data = $this->fetchBook($isbn);

        # Determine product type
        // $this->productGroup = $this->productGroups[$source['Sortimentskennzeichen']];

        $props = [
            'fromCache'    => $data['fromCache'],
            'isbn'         => $isbn,
            'translations' => $this->translations,
        ];

        return Factory::factory($data['source'], $props);
    }


    /**
     * Validates ISBNs & builds `Books` object
     *
     * @param array $isbns - A group of books' ISBNs
     * @return \PHPCBIS\Products\ProductList
     */
    public function loadBooks(array $isbns): \PHPCBIS\Products\ProductList
    {
        $books = [];

        foreach ($isbns as $isbn) {
            $books[] = $this->loadBook($isbn);
        }

        return new Books($books);
    }
}
