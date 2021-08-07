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
     * Methods
     */

    /**
     * Turns data from a single CSV file into a PHP array
     *
     * @param string $file - Source CSV file to read data from
     * @param array $headers - Header names for CSV data rows
     * @param string $delimiter - Delimiting character
     * @return array
     */
    public static function csvOpen(string $file, array $headers, string $delimiter = ';')
    {
        $data = [];

        if (($handle = fopen($file, 'r')) !== false) {
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
     * Extracts data from a single CSV file & processes it
     *
     * @param string $file - Source CSV file to read data from
     * @param string $delimiter - Delimiting character
     * @param array $headers - Header names for CSV data rows
     * @return array|bool
     */
    public static function csv2array(string $file, string $delimiter = ';', array $headers = null)
    {
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }

        # Headers as exported via 'Titelexport'
        $headers = $headers ?? [
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

        $raw = self::csvOpen($file, $headers, $delimiter);

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
            ) = self::generateInfo($infoArray);

            $array = Butler::update($array, [
                # Updating existing entries + adding blanks to prevent columns from shifting
                'Titel' => self::convertTitle($array['Titel']),
                'Untertitel' => '',
                'Mitwirkende' => '',
                'Preis' => self::convertPrice($array['Preis']),
                'Erscheinungsjahr' => $year,
                'Altersempfehlung' => $age,
                'Inhaltsbeschreibung' => '',
                'Informationen' => $info,
                'Einband' => self::convertBinding($array['Einband']),
                'Seitenzahl' => $pageCount,
                'Abmessungen' => '',
            ]);

            $data[] = self::sortArray($array);
        }

        return Butler::sort($data, 'AutorIn', 'asc');
    }


    /**
     * Turns a PHP array into CSV file
     *
     * @param array $array - Source PHP array to read data from
     * @param string $file - Destination CSV file to write data to
     * @param string $delimiter - Separator character
     * @return bool
     */
    public static function array2csv(array $array, string $file, string $delimiter = ','): bool
    {
        $header = null;

        if (($handle = fopen($file, 'w')) !== false) {
            foreach ($array as $row) {
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
     * Utilities
     */

    /**
     * Processes array containing general information,
     * applying functions to convert wanted data
     *
     * @param array $array - Source PHP array to read data from
     * @return array
     */
    protected static function generateInfo(array $array)
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
                $age = self::convertAge($entry);
                unset($array[array_search($entry, $array)]);
            }

            # Filtering page count
            if (Butler::contains($entry, ' S.')) {
                $pageCount = self::convertPageCount($entry);
                unset($array[array_search($entry, $array)]);
            }

            # Filtering year (almost always right at this point)
            if (Butler::length($entry) == 4) {
                $year = $entry;
                unset($array[array_search($entry, $array)]);
            }
        }

        $translations = json_decode(file_get_contents(__DIR__ . '/../i18n/de.json'), true);

        $strings = $translations['information'];
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
    protected static function convertTitle($string)
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
    protected static function convertAge($string)
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
    protected static function convertPageCount($string)
    {
        return (int) $string;
    }


    /**
     * Builds 'Einband' attribute as exported with pcbis.de
     *
     * @param string $string - Einband string
     * @return string
     */
    protected static function convertBinding($string)
    {
        $translations = json_decode(file_get_contents(__DIR__ . '/../i18n/de.json'), true);

        return $translations['binding'][$string];
    }


    /**
     * Builds 'Preis' attribute as exported with pcbis.de
     *
     * @param string $string - Preis string
     * @return string
     */
    protected static function convertPrice($string)
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

        $sortedArray = [];

        foreach ($sortOrder as $entry) {
            $sortedArray[$entry] = $array[$entry];
        }

        return $sortedArray;
    }
}
