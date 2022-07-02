<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products\Books\Types;

use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Products\Books\Book;

/**
 * Class Schoolbook
 *
 * KNV product category 'Schulbuch'
 */
class Schoolbook extends Book
{
    /**
     * Dataset methods
     */

    /**
     * Exports school subject
     *
     * @return string
     */
    public function subject(): string
    {
        # Maps (partial) strings to subjects
        # TODO: Multiple subjects possible?
        $subjects = [
            # Languages
            'englisch'       => 'Englisch',
            'französisch'    => 'Französisch',
            'russisch'       => 'Russisch',
            'spanisch'       => 'Spanisch',
            'italienisch'    => 'Italienisch',
            'latein'         => 'Latein',
            'altgriechisch'  => 'Altgriechisch',
            'griechisch'     => 'Griechisch',
            'deutsch'        => 'Deutsch',

            # Natural sciences
            'mathe'          => 'Mathematik',
            'biologie'       => 'Biologie',
            'chemie'         => 'Chemie',
            'physik'         => 'Physik',
            'informatik'     => 'Informatik',
            'erdkunde'       => 'Geographie',
            'geografie'      => 'Geographie',
            'geographie'     => 'Geographie',
            'heimatkunde'    => 'Sachunterricht',
            'sachunterr'     => 'Sachunterricht',

            # Social sciences
            'geschichte'     => 'Geschichte',
            'gemeinschaftsk' => 'Gemeinschaftskunde',
            'gesellschafts'  => 'Gemeinschaftskunde',
            'staatsbürger'   => 'Gemeinschaftskunde',
            'sozialkunde'    => 'Sozialkunde',
            'wirtschafts'    => 'Wirtschaftskunde',
            'hauswirtschaft' => 'Hauswirtschaft',

            # Fine arts
            'musik'          => 'Musik',
            'kunsterz'       => 'Kunst',
            'kunstunt'       => 'Kunst',

            # Ethics & religions
            'ethik'          => 'Ethik',
            'philosophie'    => 'Philosophie',
            'religion'       => 'Religion',
            'islam'          => 'Religion',
            'christl'        => 'Religion',
            'evangel'        => 'Religion',
            'kathol'         => 'Religion',
        ];

        # Create data array
        $array = [];

        # Define sources likely giving away the subject
        # (1) Multiple values
        foreach (['IndexSchlagw', 'IndexStichw'] as $source) {
            if (isset($this->data[$source])) {
                # .. in order to merge with the other candidates
                $array = array_merge($array, (array) $this->data[$source]);
            }
        }

        # (2) Single values
        $sources = [
            'Titel',
            'Kurztitel',
            'AutorSachtitel',
            'Utitel',
            'Abb',
        ];

        foreach ($sources as $source) {
            if (isset($this->data[$source])) {
                $array[] = $this->data[$source];
            }
        }

        # Iterate over collected sources ..
        foreach ($array as $string) {
            # .. as well as known subjects ..
            foreach ($subjects as $key => $value) {
                # .. and see what sticks
                if (Str::contains(Str::lower($string), $key)) {
                    return $value;
                }
            }
        }

        return '';
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Schoolbook' specific data
            'Schulfach' => $this->subject(),
        ]);
    }
}
