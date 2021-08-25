<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 2.3.0
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
     * @param string $file - Source CSV file to read data from
     * @param string $delimiter - Delimiting character
     * @param array $headers - Header names for CSV data rows
     * @param array $transcriptions - Transcribable info strings
     * @return array
     */
    public static function csv2array(string $file, string $delimiter = ';', array $headers = null, array $transcriptions = null): array
    {
        $data = [];

        if (!file_exists($file) || !is_readable($file)) {
            return $data;
        }

        # Define headers as exported via pcbis.de
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

        # Load transcriptions for info strings
        $transcriptions = $transcriptions ?? [
            '1. Aufl.' => 'Erstauflage',
            '2. Aufl.' => 'Zweitauflage',
            'Erstauflage 2019' => 'Erstauflage',
            'Sonderausg.' => 'Sonderausgabe',
            'Ktn.' => 'Karten',
            'In Box' => 'in einer Box',
            'In Metall-Box' => 'in einer Metallbox',
            'In Spielebox' => 'in einer Spielebox',
            'In Schachtel' => 'in einer Schachtel',
            'im Karton' => 'in einem Kartón',
            'm. zahlr.' => 'mit zahlreichen',
            'Zeichn v ' => 'Zeichnungen von ',
            'bunten Bild.' => 'bunten Bildern',
            'aufklappb. Bild.' => 'aufklappbaren Bildern',
            'durchg. farb.' => 'durchgehend farbige Illustrationen',
            'farb. Illustrationen' => 'farbigen Illustrationen',
            'farb. Abb.' => 'farbige Abbildungen',
            'schw.-w. Abb.' => 'schwarz-weiße Abbildungen',
            'Abb.' => 'Abbildungen',
            'Farbabb.' => 'Farbabbildungen',
            'sw Illustrationen' => 'schwarz-weißen Illustrationen',
            'sw-Illus' => 'schwarz-weißen Illustrationen',
            's/w' => 'schwarz-weiß',
            'Konturgestanzt' => 'konturgestanzt',
            'Klapp-S.' => 'Klappseiten',
            'Illustr.' => 'Illustrationen',
            'Ill.' => 'Illustrationen',
            'Illustrationen Illustrationen' => 'Illustrationen',
            'farb.' => 'farbigen',
            'z. Tl.' => 'zum Teil',
            'Unzerr.' => 'unzerreißbar',
            'Formgestanzt' => 'formgestanzt',
            'Mit ' => 'mit ',
            'm. ' => 'mit ',
            'Für ' => 'für ',
            'Kinder u. Jugendliche' => 'Kinder & Jugendliche',
            'u. ' => 'und ',
            'Durchgehend' => 'durchgehend',
            'In Kassette' => 'in einer Kassette',
            'JEWELCASE' => 'Jewelcase',
            'HALBLN' => 'Halbleinen',
            'GB#21' => '',
            'Min.' => 'Minuten',
            'Min, ' => 'Minuten, ',
            'Komplett' => 'komplett',
            'Englisch Broschur' => 'Englische Broschur',
            'Gebunden' => 'gebunden',
            'Schwarz' => 'schwarz',
            'Geblockt' => 'geblockt'
        ];

        $bindings = json_decode(file_get_contents(__DIR__ . '/../i18n/bindings.json'), true);

        $raw = self::csvOpen($file, $headers, $delimiter);

        foreach ($raw as $array) {
            # Gathering & processing generic book information
            $string = $array['Informationen'];

            # Determine separator
            $infos = Butler::split($string, ';');

            if (count($infos) === 1) {
                $infos = Butler::split($string, '.');
            }

            # Extract variables from info string
            list($info, $year, $age, $pageCount) = self::generateInfo($infos, $transcriptions);

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
     * Processes array containing general information,
     * applying functions to convert wanted data
     *
     * @param array $array - Source PHP array to read data from
     * @param array $translations - Translatable strings
     * @return array
     */
    protected static function generateInfo(array $array, array $transcriptions)
    {
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

        $array = Butler::replace($array, array_keys($transcriptions), array_values($transcriptions));

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
