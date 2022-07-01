<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products;

use Fundevogel\Pcbis\Butler;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;

use DOMDocument;
use Exception;

/**
 * Class Product
 *
 * Generic base class
 */
class Product extends ProductAbstract
{
    /**
     * Magic methods
     */

    /**
     * Print author & title when casting to string
     *
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->author())) {
            return $this->title();
        }

        # TODO: Fix this
        # return $this->author() . ': ' . $this->title();
        return '';
    }


    /**
     * Global setter
     *
     * @param string $key
     * @param mixed $value
     * @throws \Exception
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        # If method exists ..
        if (method_exists($this, $key)) {
            # .. fail request
            throw new Exception('Access read-only!');
        }

        $this->{$key} = $value;
    }


    /**
     * Global getter
     *
     * @param string $key
     * @return array|string
     */
    public function __get(string $key): array|string
    {
        # If method exists ..
        if (method_exists($this, $key)) {
            # .. use it
            return $this->{$key}();
        }

        return $this->{$key};
    }


    /**
     * Dataset methods
     */

    /**
     * Exports title
     *
     * @return string
     */
    public function title(): string
    {
        if (!isset($this->data['Titel'])) {
            if (isset($this->data['AutorSachtitel'])) {
                return $this->data['AutorSachtitel'];
            }

            return '';
        }

        return $this->data['Titel'];
    }


    /**
     * Exports subtitle
     *
     * @return string
     */
    public function subtitle(): string
    {
        if (!isset($this->data['Utitel'])) {
            return '';
        }

        return $this->data['Utitel'];
    }


    /**
     * Exports publisher(s)
     *
     * @return array|string
     */
    public function publisher(): array|string
    {
        if (!isset($this->data['IndexVerlag'])) {
            return [];
        }

        if (is_array($this->data['IndexVerlag'])) {
            $publisher = [];

            foreach ($this->data['IndexVerlag'] as $string) {
                # Skip variations
                if (Str::contains($string, ' # ')) {
                    continue;
                }

                $publisher[] = trim($string);
            }

            return $publisher;
        }

        return trim($this->data['IndexVerlag']);
    }


    /**
     * Exports description(s)
     *
     * @return array
     */
    public function description(): array
    {
        if (!isset($this->data['Text1'])) {
            return [];
        }

        # Prepare text for HTML processing
        # (1) Avoid `htmlParseStartTag: invalid element name in Entity` warnings
        # Sometimes, KNV uses '>>' & '<<' instead of quotation marks, leading to broken texts
        # See 978-3-8373-9003-2
        $text = Str::replace($this->data['Text1'], ['&gt;&gt;', '&lt;&lt;'], ['"', '"']);
        # TODO: Use additional text fields?

        # (2) Convert HTML elements
        $text = html_entity_decode($text);

        # (3) Avoid `htmlParseEntityRef: no name in Entity` warnings
        # See https://stackoverflow.com/a/14832134
        # TODO: Should be deprecated
        $text = Str::replace($text, '&', '&amp;');

        # Create DOM document & load HTML
        $dom = new DOMDocument();

        # Suppress warnings when encountering invalid HTML
        # See https://stackoverflow.com/a/41845049
        libxml_use_internal_errors(true);

        # Load prepared HTML text
        $dom->loadHtml($text);

        # Extract individual texts by ..
        $description = [];

        # (1) .. iterating over `<span>` elements and ..
        foreach ($dom->getElementsByTagName('span') as $node) {
            # (2) .. storing their content
            $description[] = utf8_decode($node->nodeValue);
        }

        return $description;
    }


    /**
     * Exports retail price (in €)
     *
     * Examples:
     * - XX    => XX,00
     * - XX.YY => XX,YY
     *
     * @return string
     */
    public function retailPrice(): string
    {
        if (!isset($this->data['PreisEurD'])) {
            return '';
        }

        return number_format((float)$this->data['PreisEurD'], 2, ',', '');
    }


    /**
     * Exports release year
     *
     * @return string
     */
    public function releaseYear(): string
    {
        if (!isset($this->data['Erschjahr'])) {
            return '';
        }

        return $this->data['Erschjahr'];
    }


    /**
     * Exports recommended minimum age (in years)
     *
     * @return string
     */
    public function age(): string
    {
        if (!isset($this->data['Alter'])) {
            return '';
        }

        $age = Str::substr($this->data['Alter'], 0, 2);

        if (Str::substr($age, 0, 1) === '0') {
            $age = Str::substr($age, 1, 1);
        }

        # TODO: Add support for months
        return 'ab ' . $age . ' Jahren';
    }


    /**
     * Exports series & volume(s)
     *
     * @return array
     */
    public function series(): array
    {
        $data = [
            'VerwieseneReihe1' => 'BandnrVerwieseneReihe1',
            'VerwieseneReihe2' => 'BandnrVerwieseneReihe2',
            'VerwieseneReihe3' => 'BandnrVerwieseneReihe3',
            'VerwieseneReihe4' => 'BandnrVerwieseneReihe4',
            'VerwieseneReihe5' => 'BandnrVerwieseneReihe5',
            'VerwieseneReihe6' => 'BandnrVerwieseneReihe6',
        ];

        $array = [];

        foreach ($data as $series => $volume) {
            # Upon first series not being present ..
            if (!isset($this->data[$series])) {
                # .. abort loop
                break;
            }

            $array[trim($this->data[$series])] = trim($this->data[$volume]);
        }

        return $array;
    }


    /**
     * Checks whether product is part of one (or more) series
     *
     * @return bool
     */
    public function isSeries(): bool
    {
        return !empty($this->series());
    }


    /**
     * Exports weight  (in g)
     *
     * @return string
     */
    public function weight(): string
    {
        if (!isset($this->data['Gewicht'])) {
            return '';
        }

        # TODO: Always grams?
        return $this->data['Gewicht'];
    }


    /**
     * Exports width (in cm)
     *
     * @return string
     */
    public function width(): string
    {
        if (!isset($this->data['Breite'])) {
            return '';
        }

        return Butler::convertMM($this->data['Breite']);
    }


    /**
     * Exports height (in cm)
     *
     * @return string
     */
    public function height(): string
    {
        if (!isset($this->data['Höhe'])) {
            return '';
        }

        return Butler::convertMM($this->data['Höhe']);
    }


    /**
     * Exports depth (in cm)
     *
     * @return string
     */
    public function depth(): string
    {
        if (!isset($this->data['Tiefe'])) {
            return '';
        }

        return Butler::convertMM($this->data['Tiefe']);
    }


    /**
     * Exports dimensions (in cm)
     *
     * Examples:
     * - 'width'
     * - 'height'
     * - 'width x height'
     * - 'width x height x depth'
     *
     * @return string
     */
    public function dimensions(): string
    {
        return A::join(array_filter([
            $this->width(),
            $this->height(),
            $this->depth(),
        ]), 'x');
    }


    /**
     * Exports language(s)
     *
     * @return array|string
     */
    public function languages(): array|string
    {
        if (!isset($this->data['Sprachschl'])) {
            return [];
        }

        $languageCodes = [
            '00' => 'Undefiniert',
            '01' => 'Deutsch',
            '02' => 'Englisch',
            '03' => 'Niederländisch/Flämisch',
            '05' => 'Dänisch',
            '06' => 'Norwegisch',
            '07' => 'Schwedisch',
            '08' => 'Isländisch',
            '09' => 'Andere Germanische',
            '10' => 'Französisch',
            '12' => 'Italienisch',
            '13' => 'Katalanisch',
            '14' => 'Spanisch',
            '16' => 'Portugiesisch',
            '17' => 'Rumänisch',
            '18' => 'Latein',
            '19' => 'Andere Romanische',
            '20' => 'Griechisch',
            '22' => 'Altgriechisch',
            '30' => 'Russisch',
            '31' => 'Bulgarisch',
            '32' => 'Serbisch/Kroatisch',
            '34' => 'Polnisch',
            '36' => 'Tschechisch',
            '37' => 'Slowakisch',
            '38' => 'Sorbisch',
            '39' => 'Andere Slawische',
            '41' => 'Finnisch',
            '42' => 'Ungarisch',
            '43' => 'Baltisch',
            '45' => 'Keltisch',
            '49' => 'Andere europäische',
            '50' => 'Hebräisch',
            '52' => 'Arabisch',
            '59' => 'Andere hamitosemitische',
            '60' => 'Türkisch',
            '62' => 'Iranische Sprachen',
            '65' => 'Japanisch',
            '66' => 'Chinesisch',
            '67' => 'Indoarische Sprachen',
            '69' => 'Sonstige asiatische',
            '90' => 'Afrikanische Sprachen',
            '94' => 'Indianersprachen',
            '97' => 'Australische/Ozeanische',
            '99' => 'Esperanto',
        ];

        if (is_array($this->data['Sprachschl'])) {
            return array_map(function (string $languageCode) use ($languageCodes) {
                # Be safe, trim strings
                return $languageCodes[trim($languageCode)];
            }, $this->data['Sprachschl']);
        }

        # Be safe, trim strings
        return $languageCodes[trim($this->data['Sprachschl'])];
    }


    /**
     * Exports type of value added tax (VAT)
     *
     * Examples:
     * - '0' = none
     * - '1' = half
     * - '2' = full
     *
     * @return string
     */
    public function vat(): string
    {
        if (!isset($this->data['Mwstknz'])) {
            return '';
        }

        return $this->data['Mwstknz'];
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return [
            # (1) Base
            'Titel'               => $this->title(),
            'Untertitel'          => $this->subtitle(),
            'Verlag'              => $this->publisher(),
            'Inhaltsbeschreibung' => $this->description(),
            'Preis'               => $this->retailPrice(),
            'Erscheinungsjahr'    => $this->releaseYear(),
            'Altersempfehlung'    => $this->age(),
            'Reihen'              => $this->series(),
            'Gewicht'             => $this->weight(),
            'Abmessungen'         => $this->dimensions(),
            'Sprachen'            => $this->languages(),
            'Mehrwehrtsteuersatz' => $this->vat(),

            # (2) Extension 'Tags'
            'Kategorien'          => $this->categories(),
            'Themen'              => $this->topics(),
        ];
    }
}
