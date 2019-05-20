<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/pcbis2pdf
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace PHPCBIS;

use PHPCBIS\Helpers\Butler;
use Doctrine\Common\Cache\FilesystemCache;

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
    const VERSION = '0.4.3';


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
     * User-Agent used when downloading book cover images
     *
     * @var string
     */
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    public function __construct(array $login = null, string $lang = 'de')
    {
        // Credentials for restricted APIs
        $this->login = $login;

        if ($login === null) {
            $this->login = $this->getLogin();
        }

        // Feel free to open a pull request to include additional language variables
        // TODO: Extending language variables by local files or other means (eg passing an array)
        $this->translations = json_decode(file_get_contents(__DIR__ . '/../languages/' . $lang . '.json'), true);
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

    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }


    /**
     * Checks whether given ISBN consists of 10 or 13 digits
     * For more advanced ways to detect valid ISBNs,
     * see https://github.com/biblys/isbn
     *
     * @param string $isbn - International Standard Book Number
     * @return boolean|InvalidArgumentException
     */
    public function validateISBN(string $isbn)
    {
        $cleanISBN = Butler::replace($isbn, '-', '');
        $length = Butler::length($cleanISBN);

        if ($length === 10 || $length === 13) {
            return true;
        }

        throw new \InvalidArgumentException('ISBN must consist of 10 or 13 digits, ' . $length . ' given (' . $isbn . ').');
    }


    /**
     * Loads credentials saved in a local JSON file as array
     *
     * @param string $fileName - Name of file to be included
     * @return array|boolean
     */
    public function getLogin(string $fileName = 'login')
    {
        if (file_exists($file = realpath('./' . $fileName . '.json'))) {
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
    public function callAPI(string $isbn)
    {
        $client = new \SoapClient('http://ws.pcbis.de/knv-2.0/services/KNVWebService?wsdl', [
            'soap_version' => SOAP_1_2,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'trace' => true,
            'exceptions' => true,
        ]);

        // For getting started with KNV's (surprisingly well documented) german API,
        // see http://www.knv.de/fileadmin/user_upload/IT/KNV_Webservice_2018.pdf
        $query = $client->WSCall([
            // Login using credentials provided by `knv.login.json`
            'LoginInfo' => $this->login,
            // Starting a new database query
            'Suchen' => [
                'Datenbank' => [
                // Basically searching all databases they got
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
                        'Suchart' => 'Genau'
                    ],
                ],
            ],
            // Reading the results of the query above
            'Lesen' => [
                // Returning the first result is alright, since given ISBN is unique
                'SatzVon' => 1,
                'SatzBis' => 1,
                'Format' => 'KNVXMLLangText',
                'AuswahlMultimediaDaten' => [
                    // We only want the best cover they got - ZOOM mode ON!
                    'mmDatenLiefern' => true,
                    'mmVarianteFilter' => 'zoom',
                ],
            ],
            // .. and logging out, that's it!
            'Logout' => true
        ]);

        if ($query->Suchergebnis->TrefferGesamt === 0) {
            return false;
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
     * Fetches book information from cache if they exist, otherwise loads them & saves to cache
     *
     * @param string $isbn - A given book's ISBN
     * @return array|boolean
     */
    public function loadBook($isbn)
    {
        $this->validateISBN($isbn);

        $driver = new FilesystemCache($this->cachePath);

        if ($driver->contains($isbn) === false) {
            $result = $this->callAPI($isbn);
            $driver->save($isbn, $result);
        }

        return $driver->fetch($isbn);
    }

    /**
     * Downloads book cover from DNB
     *
     * .. if book cover for given ISBN doesn't exist already
     *
     * @param string $isbn - International Standard Book Number
     * @param string $fileName - Filename for the image to be downloaded
     * @return boolean
     */
    public function downloadCover(string $isbn, string $fileName = null, bool $overwrite = false)
    {
        $this->validateISBN($isbn);

        if ($fileName == null) {
            $fileName = $isbn;
        }

        $file = $this->imagePath . '/' . $fileName . '.jpg';

        if (file_exists($file) && !$overwrite) {
            return true;
        }

        $url = 'https://portal.dnb.de/opac/mvb/cover.htm?isbn=' . $isbn;

        if ($handle = fopen($file, 'w')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            $result = parse_url($url);
            curl_setopt($ch, CURLOPT_REFERER, $result['scheme'] . '://' . $result['host']);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
            $raw = curl_exec($ch);
            curl_close($ch);

            if (!$raw) {
                @unlink($file);
                return false;
            }

            fwrite($handle, $raw);
            fclose($handle);

            return true;
        }

        return false;
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'AutorIn' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getAuthor(array $array)
    {
        if (Butler::missing($array, ['AutorSachtitel'])) {
            return '';
        }

        $data = Butler::split($array['AutorSachtitel'], ';');
        $authors = [];

        foreach ($data as $value) {
            $author = Butler::split($value, ',');
            $authorReverse = array_reverse($author);
            $authors[] = Butler::join($authorReverse, ' ');
        }

        return Butler::join($authors, '; ');
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Titel' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getTitle(array $array)
    {
        if (Butler::missing($array, ['Titel'])) {
            return '';
        }

        return $array['Titel'];
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Untertitel' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getSubtitle(array $array)
    {
        if (Butler::missing($array, ['Utitel'])) {
            return '';
        }

        if ($array['Utitel'] == null) {
            return '';
        }

        return $array['Utitel'];
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Verlag' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getPublisher(array $array)
    {
        if (Butler::missing($array, ['IndexVerlag'])) {
            return '';
        }

        $publisher = $array['IndexVerlag'];

        if (is_array($publisher)) {
            return trim(Butler::first($publisher));
        }

        return trim($publisher);
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Mitwirkende' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getParticipants(array $array)
    {
        if (Butler::missing($array, ['Mitarb'])) {
            return '';
        }

        $array = Butler::split($array['Mitarb'], '; ');

        foreach ($array as $x => $entry) {
            $subarray = Butler::split($entry, ': ');

            foreach ($subarray as $y => $subentry) {
                $substring = Butler::split($subentry, ', ');
                $substringReverse = array_reverse($substring);
                $subarray[$y] = Butler::join($substringReverse, ' ');
            }

            $array[$x] = Butler::join($subarray, ': ');
        }

        return Butler::join($array, '; ');
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Preis' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getPrice(array $array)
    {
        // Input: XX(.YY)
        // Output: XX,YY
        if (Butler::missing($array, ['PreisEurD'])) {
            return '';
        }

        return number_format((float) $array['PreisEurD'], 2, ',', '');
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Erscheinungsjahr' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getYear(array $array)
    {
        if (Butler::missing($array, ['Erschjahr'])) {
            return '';
        }

        return $array['Erschjahr'];
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Erscheinungsjahr' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getAge(array $array)
    {
        if (Butler::missing($array, ['Alter'])) {
            return '';
        }

        $string = Butler::substr($array['Alter'], 0, 2);

        if (Butler::substr($string, 0, 1) === '0') {
            $string = Butler::substr($string, 1, 1);
        }

      	return 'ab ' . $string . ' Jahren';
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Inhaltsbeschreibung' attribute
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getText(array $array)
    {
        if (Butler::missing($array, ['Text1'])) {
            return 'Keine Beschreibung vorhanden!';
        }

        $textArray = Butler::split($array['Text1'], 'º');

        foreach ($textArray as $index => $entry) {
            $entry = htmlspecialchars_decode($entry);
            $entry = Butler::replace($entry, '<br><br>', '. ');
            $entry = Butler::unhtml($entry);
            $textArray[$index] = $entry;

            if (Butler::length($textArray[$index]) < 130 && count($textArray) > 1) {
                unset($textArray[array_search($entry, $textArray)]);
            }
        }
        return Butler::first($textArray);
    }


    /**
     * Converts 'Abmessungen' attribute from millimeters to centimeters
     *
     * @param string $string - Abmessungen string
     * @return string
     */
    private function convertMM(string $string)
    {
        $string = $string / 10;
        $string = Butler::replace($string, '.', ',');

        return $string . 'cm';
    }


    /**
     * Processes array & builds 'Abmessungen' attribute as fetched from KNV's API
     *
     * @param array $array - Source PHP array to read data from
     * @return string
     */
    private function getDimensions(array $array)
    {
        if (Butler::missing($array, ['Breite'])) {
            return '';
        }

        if (Butler::missing($array, ['Hoehe'])) {
            return '';
        }

        $width = $this->convertMM($array['Breite']);
        $height = $this->convertMM($array['Hoehe']);

        return $width . ' x ' . $height;
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Einband' attribute
     *
     * @param string $string - Einband string
     * @return string
     */
    private function getBinding(array $array)
    {
        if (Butler::missing($array, ['Einband'])) {
            return '';
        }

        $translations = $this->translations['binding'];
        $string = $array['Einband'];

        return $translations[$string];
    }


    /**
     * Splits 'IndexSchlagw' array into categories & tags
     *
     * @param string $array - Source PHP array to read data from
     * @return string
     */
    private function separateTags(array $array)
    {
        if (Butler::missing($array, ['IndexSchlagw'])) {
            return '';
        }

        if (is_string($array['IndexSchlagw'])) {
            $array = Butler::split(trim($array['IndexSchlagw']), ';');

            return [
                Butler::contains($array[0], 'Antolin') ? '' : $array[0],
                count($array) === 2 ? $array[1] : '',
            ];
        }

        $tags = [];
        $categories = [];

        foreach ($array['IndexSchlagw'] as $entry) {
            $array = Butler::split(trim($entry), ';');

            if (count($array) === 1 && Butler::contains($array[0], 'Antolin')) {
                continue;
            }

            $tags[] = $array[0];
            $categories[] = $array[1];
        }

        // $categories might hold duplicates
        $categories = array_unique($categories);

        return [$tags, $categories];
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Kategorien' attribute
     *
     * @param string $array - Source PHP array to read data from
     * @return string
     */
    private function getCategories(array $array)
    {
        $array = $this->separateTags($array)[1];

        return Butler::join($array, ', ');
    }


    /**
     * Processes array (fetched from KNV's API) & builds 'Schlagworte' attribute
     *
     * @param string $array - Source PHP array to read data from
     * @return string
     */
    private function getTags(array $array)
    {
        $array = $this->separateTags($array)[0];

        return Butler::join($array, ', ');
    }


    /**
     * Builds an array with KNV information
     *
     * @param array $dataInput - Input that should be processed
     * @return array
     */
    public function processData(array $dataInput = null)
    {
        if ($dataInput == null) {
            throw new \InvalidArgumentException('No data to process!');
        }

        try {
            $dataOutput = [
                'AutorIn' => $this->getAuthor($dataInput),
                'Titel' => $this->getTitle($dataInput),
                'Untertitel' => $this->getSubtitle($dataInput),
                'Verlag' => $this->getPublisher($dataInput),
                'Mitwirkende' => $this->getParticipants($dataInput),
                'Preis' => $this->getPrice($dataInput),
                'Erscheinungsjahr' => $this->getYear($dataInput),
                'Altersempfehlung' => $this->getAge($dataInput),
                'Inhaltsbeschreibung' => $this->getText($dataInput),
                'Abmessungen' => $this->getDimensions($dataInput),
                'Einband' => $this->getBinding($dataInput),
                'Kategorien' => $this->getCategories($dataInput),
                'Schlagworte' => $this->getTags($dataInput),
            ];
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage(), "\n";
        }

        return $dataOutput;
    }
}
