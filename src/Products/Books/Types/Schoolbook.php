<?php

namespace Fundevogel\Pcbis\Products\Books\Types;

use Fundevogel\Pcbis\Helpers\Butler;
use Fundevogel\Pcbis\Products\Books\Book;


/**
 * Class Schoolbook
 *
 * KNV product category 'Schulbuch'
 */
class Schoolbook extends Book {
    /**
     * Properties
     */

    /**
     * School subject
     *
     * @var string
     */
    protected $subject;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->subject = $this->buildSubject();
    }


    /**
     * Methods
     */

    /**
     * Builds school subject
     *
     * @return string
     */
    protected function buildSubject(): string
    {
        # Store typical subjects
        $subjects = [
            # Languages
            'englisch'    => 'Englisch',
            'französisch' => 'Französisch',
            'russisch'    => 'Russisch',
            'spanisch'    => 'Spanisch',
            'italienisch' => 'Italienisch',
            'latein'      => 'Latein',
            'griechisch'  => 'Griechisch',
            'deutsch'     => 'Deutsch',

            # Natural sciences
            'mathe'       => 'Mathematik',
            'biologie'    => 'Biologie',
            'chemie'      => 'Chemie',
            'physik'      => 'Physik',
            'informatik'  => 'Informatik',
            'erdkunde'    => 'Geographie',
            'geografie'   => 'Geographie',
            'geographie'  => 'Geographie',
            'heimatkunde' => 'Sachunterricht',
            'sachunterr'  => 'Sachunterricht',

            # Social sciences
            'geschichte'     => 'Geschichte',
            'gemeinschaftsk' => 'Gemeinschaftskunde',
            'gesellschafts'  => 'Gemeinschaftskunde',
            'staatsbürger'   => 'Gemeinschaftskunde',
            'sozialkunde'    => 'Sozialkunde',
            'wirtschafts'    => 'Wirtschaftskunde',
            'hauswirtschaft' => 'Hauswirtschaft',

            # Fine arts
            'musik'    => 'Musik',
            'kunsterz' => 'Kunst',
            'kunstunt' => 'Kunst',

            # Ethics & religions
            'ethik'       => 'Ethik',
            'philosophie' => 'Philosophie',
            'religion'    => 'Religion',
            'islam'       => 'Religion',
            'christl'     => 'Religion',
            'evangel'     => 'Religion',
            'kathol'      => 'Religion',
        ];

        # Include different sources (by likeliness of giving away the subject)
        $array = [];

        # (1) Full title
        if (isset($this->source['Titel'])) {
            $array[] = $this->source['Titel'];
        }

        # (2) Short title
        if (isset($this->source['Kurztitel'])) {
            $array[] = $this->source['Kurztitel'];
        }

        # (3) Author (may contain full title)
        if (isset($this->source['AutorSachtitel'])) {
            $array[] = $this->source['AutorSachtitel'];
        }

        # (4) Subtitle
        if (isset($this->source['Utitel'])) {
            $array[] = $this->source['Utitel'];
        }

        # (5) Tags
        if (isset($this->source['IndexSchlagw'])) {
            # Differentiate between tags being an array ..
            $tags = $this->source['IndexSchlagw'];

            if (is_string($tags) === true) {
                # .. or a string, in which case turn them into an array ..
                $tags = (array)$this->source['IndexSchlagw'];
            }

            # .. in order to merge with the other candidates
            $array = array_merge($array, $tags);
        }

        if (isset($this->source['IndexStichw'])) {
            # Differentiate between tags being an array ..
            $tags = $this->source['IndexStichw'];

            if (is_string($tags) === true) {
                # .. or a string, in which case turn them into an array ..
                $tags = (array)$this->source['IndexStichw'];
            }

            # .. in order to merge with the other candidates
            $array = array_merge($array, $tags);
        }

        # (4) Miscellaneous
        if (isset($this->source['Abb'])) {
            $array[] = $this->source['Abb'];
        }

        # Determine subject by looping over collected strings ..
        foreach ($array as $string) {
            # .. as well as known subjects ..
            foreach ($subjects as $key => $value) {
                # .. and see what sticks
                if (Butler::contains(Butler::lower($string), $key)) {
                    return $value;
                }
            }
        }

        return '';
    }


    /**
     * Exports school subject
     *
     * @return string
     */
    public function subject(): string
    {
        return $this->subject;
    }


    /**
     * Overrides
     */

    /**
     * Exports all data
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Book' dataset
            parent::export($asArray), [
            # (2) 'Schoolbook' specific data
            'Schulfach' => $this->subject(),
        ]);
    }
}
