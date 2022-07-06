<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Traits\OlaStatus;
use Fundevogel\Pcbis\Traits\People;
use Fundevogel\Pcbis\Traits\Tags;
use Fundevogel\Pcbis\Utilities\Butler;

use DOMDocument;

/**
 * Class Product
 *
 * Generic base class for products
 */
abstract class Product
{
    /**
     * Traits
     */

    use OlaStatus;
    use People;
    use Tags;


    /**
     * Properties
     */

    /**
     * Product EAN/ISBN
     *
     * @var string
     */
    protected $identifier;


    /**
     * Constructor
     *
     * @param array $data Source data as fetched from KNV's API
     * @param \Fundevogel\Pcbis\Api\Webservice $api Object granting access to KNV's API
     * @return void
     */
    public function __construct(public array $data, protected ?Webservice $api = null)
    {
        # If not specified ..
        if (is_null($this->api)) {
            # .. invoke API client in offline mode
            $this->api = new Webservice();
        }

        # Store product EAN/ISBN
        $this->identifier = $this->data['EAN'];

        # If present ..
        if (class_exists('Nicebooks\Isbn\Isbn')) {
            # .. attempt to ..
            try {
                # .. format product EAN/ISBN using third-party tools
                $this->identifier = \Nicebooks\Isbn\Isbn::of($this->identifier)->format();
            } catch (\Nicebooks\Isbn\Exception\InvalidIsbnException $e) {
            }
        }

        # Add startup hook
        $this->setup();
    }


    /**
     * Setup hook
     *
     * @return void
     */
    public function setup(): void
    {
        $this->people = $this->setUpPeople();
        $this->tags   = $this->setUpTags();
    }


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
        # Fetch author object
        $author = $this->author();

        # If present ..
        if ($author->exists()) {
            # .. print author(s) & product title
            return sprintf('%s: %s', $author->toString(', '), $this->title());
        }

        # .. otherwise, only product title
        return $this->title();
    }


    /**
     * Methods
     */

    /**
     * Checks whether product has a predecessor
     *
     * @return bool
     */
    public function hasDowngrade(): bool
    {
        return $this->data['VorherigeAuflageGtin'] != '';
    }


    /**
     * Checks whether product has a successor
     *
     * @return bool
     */
    public function hasUpgrade(): bool
    {
        return $this->data['NeueAuflageGtin'] != '';
    }


    /**
     * Type detection helper
     *
     * @return string
     */
    protected function type(): string
    {
        # Extract class name (= product type)
        return A::last(explode('\\', get_class($this)));
    }


    /**
     * Checks whether this is a book
     *
     * @return bool
     */
    public function isBook(): bool
    {
        return in_array($this->type(), [
            # Base
            'Book',

            # Subset
            'Ebook',
            'Hardcover',
            'Schoolbook',
            'Softcover',
        ]);
    }


    /**
     * Checks whether this is an eBook
     *
     * @return bool
     */
    public function isEbook(): bool
    {
        return $this->type() == 'Ebook';
    }


    /**
     * Checks whether this is a hardcover book
     *
     * @return bool
     */
    public function isHardcover(): bool
    {
        return $this->type() == 'Hardcover';
    }


    /**
     * Checks whether this is a schoolbook
     *
     * @return bool
     */
    public function isSchoolbook(): bool
    {
        return $this->type() == 'Schoolbook';
    }


    /**
     * Checks whether this is a softcover book
     *
     * @return bool
     */
    public function isSoftcover(): bool
    {
        return $this->type() == 'Softcover';
    }


    /**
     * Checks whether this is a medium
     *
     * @return bool
     */
    public function isMedia(): bool
    {
        return in_array($this->type(), [
            # Base
            'Medium',

            # Subset
            'Audiobook',
            'Movie',
            'Music',
            'Sound',
        ]);
    }


    /**
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->type() == 'Audiobook';
    }


    /**
     * Checks whether this is a movie
     *
     * @return bool
     */
    public function isMovie(): bool
    {
        return $this->type() == 'Movie';
    }


    /**
     * Checks whether this is music
     *
     * @return bool
     */
    public function isMusic(): bool
    {
        return $this->type() == 'Music';
    }


    /**
     * Checks whether this is a sound storage medium
     *
     * @return bool
     */
    public function isSound(): bool
    {
        return $this->type() == 'Sound';
    }


    /**
     * Checks whether this is an item
     *
     * @return bool
     */
    public function isItem(): bool
    {
        return in_array($this->type(), [
            # Base
            'Item',

            # Subset
            'Boardgame',
            'Calendar',
            'Map',
            'Nonbook',
            'Notes',
            'Software',
            'Stationery',
            'Toy',
            'Videogame',
        ]);
    }


    /**
     * Checks whether this is a boardgame
     *
     * @return bool
     */
    public function isBoardgame(): bool
    {
        return $this->type() == 'Boardgame';
    }


    /**
     * Checks whether this is a calendar
     *
     * @return bool
     */
    public function isCalendar(): bool
    {
        return $this->type() == 'Calendar';
    }


    /**
     * Checks whether this is a map
     *
     * @return bool
     */
    public function isMap(): bool
    {
        return $this->type() == 'Map';
    }


    /**
     * Checks whether this is a generic item
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return $this->type() == 'Nonbook';
    }


    /**
     * Checks whether these are notes
     *
     * @return bool
     */
    public function isNotes(): bool
    {
        return $this->type() == 'Notes';
    }


    /**
     * Checks whether this is software
     *
     * @return bool
     */
    public function isSoftware(): bool
    {
        return $this->type() == 'Software';
    }


    /**
     * Checks whether this is stationery
     *
     * @return bool
     */
    public function isStationery(): bool
    {
        return $this->type() == 'Stationery';
    }


    /**
     * Checks whether this is a toy
     *
     * @return bool
     */
    public function isToy(): bool
    {
        return $this->type() == 'Toy';
    }


    /**
     * Checks whether this is a videogame
     *
     * @return bool
     */
    public function isVideogame(): bool
    {
        return $this->type() == 'Videogame';
    }


    /**
     * Exports OLA record
     *
     * @param string $type OLA type (either 'anfrage', 'bestellung' or 'storno')
     * @param int $quantity Number of products to be delivered
     * @return \Fundevogel\Pcbis\Api\Ola
     */
    public function ola(int $quantity = 1, string $type = 'anfrage'): Ola
    {
        return new Ola($this->api->ola($this->identifier, $quantity, $type));
    }


    /**
     * Checks whether KNV 'Meldenummer' is present
     *
     * @return bool
     */
    public function hasOlaCode(): bool
    {
        return $this->data['Mnr'] != '';
    }


    /**
     * Exports KNV 'Meldenummer' (if present)
     *
     * @return string
     */
    public function olaCode(): string
    {
        return $this->data['Mnr'];
    }


    /**
     * Exports KNV 'Meldetext' (if present)
     *
     * @return string
     */
    public function olaMessage(): string
    {
        if (array_key_exists($this->olaCode(), $this->olaMessages)) {
            return $this->olaMessages[$this->olaCode()];
        }

        return $this->ola()->olaMessage();
    }


    /**
     * Checks whether product is available (= purchasable)
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode(), $this->available);
        }

        return $this->ola()->isAvailable();
    }


    /**
     * Checks whether product is permanently unavailable
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode(), $this->unavailable);
        }

        return $this->ola->isUnavailable();
    }


    /**
     * Dataset methods
     */

    /**
     * Exports European Article Number (EAN)
     *
     * @return string
     */
    public function ean(): string
    {
        return $this->identifier;
    }


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
        # Create data array
        $description = [];

        # Iterate over possible text field keys
        foreach (['Text1', 'Text2', 'Text3', 'Text4', 'Text15'] as $key) {
            # If key not present in data ..
            if (!isset($this->data[$key])) {
                # .. skip it
                continue;
            }

            # Prepare text for HTML processing
            # (1) Avoid `htmlParseStartTag: invalid element name in Entity` warnings
            $text = Str::replace($this->data[$key], ['&gt;&gt;', '&lt;&lt;'], ['"', '"']);
            # Note: Sometimes, KNV uses '>>' & '<<' instead of quotation marks, leading to broken texts
            # See 978-3-8373-9003-2

            # (2) Convert breakline tags
            $text = Str::replace($text, '<br>', "\n");

            # Create DOM document & load HTML
            $dom = new DOMDocument();

            # Suppress warnings when encountering invalid HTML
            # See https://stackoverflow.com/a/41845049
            libxml_use_internal_errors(true);

            # Load prepared HTML text
            $dom->loadHtml($text);

            # Iterate over `span` elements ..
            foreach ($dom->getElementsByTagName('span') as $node) {
                # (1) .. decoding them as UTF-8
                # (2) .. removing unnecessary whitespaces
                $description[] = trim(utf8_decode($node->nodeValue));
            }
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
            # If series is present ..
            if (isset($this->data[$series])) {
                # .. store it, along with empty volume
                $array[trim($this->data[$series])] = '';

                # If volume is also present ..
                if (isset($this->data[$volume])) {
                    # .. add it to its series
                    $array[trim($this->data[$series])] = trim($this->data[$volume]);
                }
            }
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

        $vatCodes = [
            '0' => 'kein',
            '1' => 'halb',
            '2' => 'voll',
        ];

        return $vatCodes[$this->data['Mwstknz']];
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
            'EAN'                 => $this->ean(),
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
