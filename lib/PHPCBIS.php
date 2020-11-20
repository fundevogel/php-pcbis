<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/pcbis2pdf
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace PHPCBIS;

use PHPCBIS\Helpers\Butler;

/**
 * Class PHPCBIS
 *
 * Retrieves information from KNV's API, makes the result human-readable &
 * downloads book covers from the German National Library
 *
 * @package PHPCBIS
 */

class PHPCBIS
{
    /**
     * Current version number of PHPCBIS
     */
    const VERSION = '1.4.0';


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
     * Path to downloaded book cover images
     *
     * @var string
     */
    private $imagePath = './images';


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
        $this->client = new \SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
            'soap_version' => SOAP_1_2,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'trace' => true,
            'exceptions' => true,
        ]);

        # Insert credentials for KNV's API
        if ($credentials === null) {
            throw new \Exception('Please provide valid login credentials.');
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

    public function setImagePath(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }

    public function getImagePath()
    {
        return $this->imagePath;
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
     * @return string|InvalidArgumentException
     */
    private function validateISBN(string $isbn): string
    {
        if (Butler::length($isbn) === 13 && Butler::startsWith($isbn, '4')) {
            # Most likely non-convertable EAN
            return $isbn;
        }

        $isbn = new \Biblys\Isbn\Isbn($isbn);

        try {
            $isbn->validate();
            $isbn = $isbn->format('ISBN-13');
        } catch(\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return $isbn;
    }


    /**
     * Uses credentials to log into KNV's API & generates a sessionID
     *
     * @param array $credentials
     * @return string|InvalidArgumentException
     */
    private function logIn(array $credentials): string
    {
        try {
            $query = $this->client->WSCall([
                'LoginInfo' => $credentials,
            ]);
        } catch (\SoapFault $e) {
            throw new \InvalidArgumentException($e->getMessage());
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
     * Fetches raw book data from KNV
     *
     * .. if book for given ISBN exists
     *
     * @param string $isbn
     * @return array|Exception
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
     * Fetches book information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $isbn - A given book's ISBN
     * @return array
     */
    private function fetchBook(string $isbn): array
    {
        $driver = new \Doctrine\Common\Cache\FilesystemCache($this->cachePath);
        $cached = $driver->contains($isbn);

        if (!$cached) {
            $result = $this->fetchData($isbn);
            $driver->save($isbn, $result);
        }

        return [
            'data' => $driver->fetch($isbn),
            'cached' => $cached,
        ];
    }


    /**
     * Validates ISBN & builds `\PHPCBIS\Book` object
     *
     * @param string $isbn - A given book's ISBN
     * @return \PHPCBIS\Book
     */
    public function loadBook(string $isbn): \PHPCBIS\Book
    {
        $isbn = $this->validateISBN($isbn);
        $book = $this->fetchBook($isbn);

        return new Book(
            $isbn,
            $book['data'],
            $this->imagePath,
            $this->translations,
            $book['cached']
        );
    }


    /**
     * Validates ISBNs & builds `\PHPCBIS\Books` object
     *
     * @param array $isbns - A group of books' ISBNs
     * @return \PHPCBIS\Books
     */
    public function loadBooks(array $isbns): \PHPCBIS\Books
    {
        $books = [];

        foreach ($isbns as $isbn) {
            $books[] = $this->loadBook($isbn);
        }

        return new Books($books);
    }
}
