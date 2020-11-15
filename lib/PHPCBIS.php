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
    const VERSION = '0.9.0';


    /**
     * Path to cached book information received from KNV's API
     *
     * @var string
     */
    private $cachePath = './.cache';


    /**
     * Path to downloaded book cover images
     *
     * @var string
     */
    private $imagePath = './images';


    /**
     * Translatable strings
     *
     * @var array
     */
    private $translations = [
        'BUCH' => 'gebunden',
        'CD'   => 'CD',
        'CRD'  => 'Nonbook',
        'GEB'  => 'gebunden',
        'GEH'  => 'geheftet',
        'HL'   => 'Halbleinen',
        'KT'   => 'kartoniert',
        'LN'   => 'Leinen',
        'NON'  => 'Nonbook',
        'PP'   => 'Pappband',
        'SPL'  => 'Spiel',
    ];


    /**
     * Constructor
     */

    public function __construct(array $credentials = null, array $translations = [])
    {
        // Credentials for KNV's restricted API
        $this->credentials = $credentials;

        if ($credentials === null) {
            $this->credentials = $this->authenticate();
        }

        if (!empty($translations)) {
            $this->translations = $translations;
        }
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

    public function getTranslations()
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
    public function validateISBN(string $isbn)
    {
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
     * Loads credentials saved in a local JSON file as array
     *
     * @param string $fileName - Name of file to be included
     * @return array|Exception
     */
    private function authenticate()
    {
        if (file_exists($file = realpath('./login.json'))) {
            $json = file_get_contents($file);
            $array = json_decode($json, true);

            return $array;
        }

        throw new \Exception('Please provide valid login credentials.');
    }


    /**
     * Returns raw book data from KNV
     *
     * .. if book for given ISBN exists
     *
     * @param string $isbn
     * @return array|Exception
     */
    public function fetchData(string $isbn)
    {
        $client = new \SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
            'soap_version' => SOAP_1_2,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'trace' => true,
            'exceptions' => true,
        ]);

        // For getting started with KNV's (surprisingly well documented) german API,
        // see http://www.knv-zeitfracht.de/wp-content/uploads/2020/07/Webservice_2.0.pdf
        $query = $client->WSCall([
            // Login using credentials provided by `login.json`
            'LoginInfo' => $this->credentials,
            // Starting a new database query
            'Suchen' => [
                // Basically searching all databases they got
                'Datenbank' => [
                    'KNV',
                    'KNVBG',
                    'BakerTaylor',
                    'Gardners',
                ],
                'Suche' => [
                    'SimpleTerm' => [
                        // Simple search suffices as from exported CSV,
                        // we already know they know .. you know?
                        'Suchfeld' => 'ISBN',
                        'Suchwert' => $isbn,
                        'Schwert2' => '',
                        'Suchart' => 'Genau',
                    ],
                ],
            ],
            // Reading the results of the query above
            'Lesen' => [
                // Returning the first result is alright, since given ISBN is unique
                'SatzVon' => 1,
                'SatzBis' => 1,
                'Format' => 'KNVXMLLangText',
            ],
            // .. and logging out, that's it!
            'Logout' => true,
        ]);

        if ($query->Suchergebnis->TrefferGesamt === 0) {
            throw new \Exception('No database entry found.');
        }

        // Getting raw XML response & preparing it to be loaded by SimpleXML
        $result = $query->Daten->Datensaetze->Record->ArtikelDaten;
        $result = Butler::replace($result, '&', '&amp;');

        // XML to JSON to PHP array - we want its last entry
        $xml = simplexml_load_string($result);
        $json = json_encode($xml);
        $array = (json_decode($json, true));

        return Butler::last($array);
    }


    /**
     * Fetches book information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $isbn - A given book's ISBN
     * @return array|bool
     */
    public function loadBook($isbn)
    {
        $isbn = $this->validateISBN($isbn);

        $driver = new \Doctrine\Common\Cache\FilesystemCache($this->cachePath);

        if ($driver->contains($isbn) === false) {
            $result = $this->fetchData($isbn);
            $driver->save($isbn, $result);
        }

        return new Book(
            $driver->fetch($isbn),
            $this->imagePath,
            $this->translations,
        );
    }
}
