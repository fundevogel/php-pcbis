<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis;

use Pcbis\Helpers\Butler;


/**
 * Class Spreadsheet
 *
 * Provides methods to extract information from CSV files
 * as exported by pcbis.de function 'Titelexport'
 *
 * @package PHPCBIS
 */

class Spreadsheets
{
    /**
     * Properties
     */

    /**
     * Raw data
     *
     * @var array
     */
    private $data = null;


    /**
     * Sort order for CSV output file headers
     *
     * @var array
     */
    private $sortOrder = [
        'AutorIn',
        'Titel',
        'Untertitel',
        'Verlag',
        'Mitwirkende',
        'Preis',
        'Erscheinungsjahr',
        'ISBN',
        'Altersempfehlung',
        'Inhaltsbeschreibung',
        'Informationen',
        'Einband',
        'Seitenzahl',
        'Abmessungen',
        '@Cover',
        'Cover DNB',
        'Cover KNV',
    ];


    /**
     * Translations for various CSV values
     *
     * @var array
     */
    private $translations = null;


    /**
     * Constructor
     */

    public function __construct(array $translations = null)
    {
        # If not provided, load default translations
        if ($translations === null) {
            $this->translations = json_decode(file_get_contents(__DIR__ . '/../i18n/de.json'), true);
        }
    }


    /**
     * Setters & getters
     */

    public function setSortOrder(array $sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }


    public function getSortOrder()
    {
        return $this->sortOrder;
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
     * Turns CSV data into a PHP array
     *
     * @param string $input - Source CSV file to read data from
     * @param string $delimiter - Delimiting character
     * @return array
     */
    public static function csv2array(string $input, string $delimiter = ';')
    {
        if (!file_exists($input) || !is_readable($input)) {
            return false;
        }

        # Headers as exported via 'Titelexport'
        $headers = [
            'AutorIn',
            'Titel',
            'Verlag',
            'ISBN',
            'Einband',
            'Preis',
            'Meldenummer',
            'SortRabatt',
            'Gewicht',
            'Informationen',
            'Zusatz',
            'Kommentar'
        ];

        $data = [];

        if (($handle = fopen($input, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (count($row) === 1) {
                    continue;
                }

                $row = array_map('utf8_encode', $row);
                $data[] = array_combine($headers, $row);
            }

            fclose($handle);
        }

        return $data;
    }


    /**
     * Turns a PHP array into CSV file
     *
     * @param array $data - Source PHP array to read data from
     * @param string $output - Destination CSV file to write data to
     * @param string $delimiter - Delimiting character
     * @return Stream
     */
    public static function array2csv(array $dataInput = null, string $output, string $delimiter = ',')
    {
        if ($dataInput === null) {
            throw new \InvalidArgumentException('No data given to process.');
        }

        $header = null;

        if (($handle = fopen($output, 'w')) !== false) {
            foreach ($dataInput as $row) {
                $headerArray = array_keys($row);

                if (!$header) {
                    fputcsv($handle, $headerArray, $delimiter);
                    $header = true;
                }

                fputcsv($handle, $row, $delimiter);
            }

            fclose($handle);
        }

        return true;
    }


    /**
     * Loads data from CSV file
     *
     * @param string $csvFile - Path to CSV source file
     * @return bool
     */
    public function load(string $csvFile): bool
    {
        if (file_exists($csvFile)) {
            $this->data = self::csv2array($csvFile);

            return true;
        }

        return false;
    }


    /**
     * Processes array containing general information,
     * applying functions to convert wanted data
     *
     * @param array $array - Source PHP array to read data from
     * @return array
     */
    protected function generateInfo(array $array)
    {
        $age = 'Keine Altersangabe';
        $pageCount = '';
        $year = '';

        foreach ($array as $entry) {
            # Remove garbled book dimensions
            if (Butler::contains($entry, ' cm') || Butler::contains($entry, ' mm')) {
                unset($array[array_search($entry, $array)]);
            }

            # Filtering age
            if (Butler::contains($entry, ' J.') || Butler::contains($entry, ' Mon.')) {
                $age = $this->convertAge($entry);
                unset($array[array_search($entry, $array)]);
            }

            # Filtering page count
            if (Butler::contains($entry, ' S.')) {
                $pageCount = $this->convertPageCount($entry);
                unset($array[array_search($entry, $array)]);
            }

            # Filtering year (almost always right at this point)
            if (Butler::length($entry) == 4) {
                $year = $entry;
                unset($array[array_search($entry, $array)]);
            }
        }

        $strings = $this->translations['information'];
        $array = Butler::replace($array,
            array_keys($strings),
            array_values($strings)
        );

        $info = ucfirst(implode(', ', $array));

        if (Butler::length($info) > 0) {
            $info = Butler::replace($info, '.', '') . '.';
        }

        return [
            $info,
            $year,
            $age,
            $pageCount,
        ];
    }


    /**
     * Builds 'Titel' attribute as exported with pcbis.de
     *
     * @param string $string - Title string
     * @return string
     */
    protected function convertTitle($string)
    {
        # Input: Book title.
        # Output: Book title
        return Butler::substr($string, 0, -1);
    }


    /**
     * Builds 'Altersangabe' attribute as exported with pcbis.de
     *
     * @param string $string - Altersangabe string
     * @return string
     */
    protected function convertAge($string)
    {
      	$string = Butler::replace($string, 'J.', 'Jahren');
      	$string = Butler::replace($string, 'Mon.', 'Monaten');
      	$string = Butler::replace($string, '-', ' bis ');
      	$string = Butler::replace($string, 'u.', '&');

      	return $string;
    }


    /**
     * Builds 'Seitenzahl' attribute as exported with pcbis.de
     *
     * @param string $string - Seitenzahl string
     * @return string
     */
    protected function convertPageCount($string)
    {
        return (int) $string;
    }


    /**
     * Builds 'Einband' attribute as exported with pcbis.de
     *
     * @param string $string - Einband string
     * @return string
     */
    protected function convertBinding($string)
    {
        $translations = $this->translations['binding'];
        $string = $translations[$string];

        return $string;
    }


    /**
     * Builds 'Preis' attribute as exported with pcbis.de
     *
     * @param string $string - Preis string
     * @return string
     */
    protected function convertPrice($string)
    {
        # Input: XX.YY EUR
        # Output: XX,YY €
        $string = Butler::replace($string, 'EUR', '€');
        $string = Butler::replace($string, '.', ',');

        return $string;
    }


    /**
     * Sorts a given array holding book information by certain sort order
     *
     * @param array $array - Input that should be sorted
     * @return array
     * TODO: https://www.php.net/manual/en/function.usort.php#25360
     */
    protected static function sortArray(array $array)
    {
        $sortedArray = [];

        $sortOrder = [
            'AutorIn',
            'Titel',
            'Untertitel',
            'Verlag',
            'Mitwirkende',
            'Preis',
            'Erscheinungsjahr',
            'ISBN',
            'Altersempfehlung',
            'Inhaltsbeschreibung',
            'Informationen',
            'Einband',
            'Seitenzahl',
            'Abmessungen',
        ];

        foreach ($sortOrder as $entry) {
            $sortedArray[$entry] = $array[$entry];
        }

        return $sortedArray;
    }


    /**
     * Exports data being extracted from CSV data
     *
     * @param array $raw - Input that should be processed
     * @return array|InvalidArgumentException
     */
    public function export(array $raw = null)
    {
        if ($raw === null) {
            if ($this->data === null) {
                throw new \InvalidArgumentException('No data given to process.');
            }

            $raw = $this->data;
        }

        $data = [];

        foreach ($raw as $array) {
            # Gathering & processing generic book information
            $infoString = $array['Informationen'];
            $infoArray = Butler::split($infoString, ';');

            if (count($infoArray) === 1) {
                $infoArray = Butler::split($infoString, '.');
            }

            # Extracting variables from information string
            list(
                $info,
                $year,
                $age,
                $pageCount
            ) = $this->generateInfo($infoArray);

            $array = Butler::update($array, [
                # Updating existing entries + adding blanks to prevent columns from shifting
                'Titel' => $this->convertTitle($array['Titel']),
                'Untertitel' => '',
                'Mitwirkende' => '',
                'Preis' => $this->convertPrice($array['Preis']),
                'Erscheinungsjahr' => $year,
                'Altersempfehlung' => $age,
                'Inhaltsbeschreibung' => '',
                'Informationen' => $info,
                'Einband' => $this->convertBinding($array['Einband']),
                'Seitenzahl' => $pageCount,
                'Abmessungen' => '',
            ]);

            $data[] = $this->sortArray($array);
        }

        return Butler::sort($data, 'AutorIn', 'asc');
    }
}
