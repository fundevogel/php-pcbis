<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 2.5.2
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
    public static function csvOpen(string $file, ?array $headers = null, string $delimiter = ';'): array
    {
        # If no headers available ..
        if (empty($headers)) {
            # .. determine them & return file contents
            # (1) Load file contents
            $lines = file($file);

            # (2) Determine header row
            $lines[0] = str_replace("\xEF\xBB\xBF", '', $lines[0]);

            # (3) Extract CSV data
            $csv = array_map(function($d) use ($delimiter) {
                # Encode CSV data as UTF-8 (if necessary)
                if (Butler::encoding($d) != 'UTF-8') {
                    $d = utf8_encode($d);
                }

                return str_getcsv($d, $delimiter);
            }, $lines);

            # (4) Add header names
            array_walk($csv, function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            });

            array_shift($csv);

            return $csv;
        }

        $data = [];

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (count($row) === 1) {
                    continue;
                }

                # Encode CSV data as UTF-8 (if necessary)
                if (Butler::encoding($row[0]) !== 'UTF-8') {
                    $row = array_map('utf8_encode', $row);
                }

                $data[] = array_combine($headers, $row);
            }

            fclose($handle);
        }

        return $data;
    }


    /**
     * Extracts data from a single CSV file & processes it
     *
     * This only works with files exported from `pcbis.de`
     *
     * @param string $file - Source CSV file to read data from
     * @param string $delimiter - Delimiting character
     * @return array
     */
    public static function csv2array(string $file, string $delimiter = ';'): array
    {
        $data = [];

        if (!file_exists($file) || !is_readable($file)) {
            return $data;
        }

        # Define headers as exported via pcbis.de
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

        $bindings = json_decode(file_get_contents(__DIR__ . '/../resources/binding_codes.json'), true);

        foreach (self::csvOpen($file, $headers, $delimiter) as $array) {
            # Gathering & processing generic book information
            $string = $array['Informationen'];

            # Determine separator
            $infos = Butler::split($string, ';');

            if (count($infos) === 1) {
                $infos = Butler::split($string, '.');
            }

            # Extract variables from info string
            $age = 'Keine Altersangabe';
            $pageCount = '';
            $year = '';

            foreach ($array as $entry) {
                # Remove garbled book dimensions
                if (Butler::contains($entry, ' cm') || Butler::contains($entry, ' mm')) {
                    unset($array[array_search($entry, $array)]);
                }

                # Filter age
                if (Butler::contains($entry, ' J.') || Butler::contains($entry, ' Mon.')) {
                    $age = self::convertAge($entry);
                    unset($array[array_search($entry, $array)]);
                }

                # Filter page count
                if (Butler::contains($entry, ' S.')) {
                    $pageCount = self::convertPageCount($entry);
                    unset($array[array_search($entry, $array)]);
                }

                # Filter year (almost always right at this point)
                if (Butler::length($entry) == 4) {
                    $year = $entry;
                    unset($array[array_search($entry, $array)]);
                }
            }

            $info = ucfirst(implode(', ', $array));

            if (Butler::length($info) > 0) {
                $info = Butler::replace($info, '.', '') . '.';
            }

            $array = Butler::update($array, [
                # Add blanks to prevent column shifts
                'Titel' => self::convertTitle($array['Titel']),
                'Untertitel' => '',
                'Mitwirkende' => '',
                'Preis' => self::convertPrice($array['Preis']),
                'Erscheinungsjahr' => $year,
                'Altersempfehlung' => $age,
                'Inhaltsbeschreibung' => '',
                'Informationen' => $info,
                'Einband' => $bindings[$array['Einband']],
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
                    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
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
     *
     * @return array|string
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
     */
    protected static function convertPageCount($string): int
    {
        return (int) $string;
    }


    /**
     * Builds 'Preis' attribute as exported with pcbis.de
     *
     * @param string $string - Preis string
     *
     * @return array|string
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
