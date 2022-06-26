<?php

namespace Fundevogel\Pcbis\Products;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Butler;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Interfaces\Exportable;
use Fundevogel\Pcbis\Interfaces\Sociable;
use Fundevogel\Pcbis\Interfaces\Taggable;
use Fundevogel\Pcbis\Traits\OlaStatus;
use Fundevogel\Pcbis\Traits\People;
use Fundevogel\Pcbis\Traits\Tags;
use Fundevogel\Pcbis\Traits\Type;

use DOMDocument;


/**
 * Class Product
 *
 * Serves as template for products
 */
abstract class Product implements Exportable, Sociable, Taggable
{
    /**
     * Traits
     */

    use OlaStatus;
    use People;
    use Tags;
    use Type;


    /**
     * Properties
     */

    /**
     * Object granting access to KNV's API
     *
     * @var \Fundevogel\Pcbis\Webservice
     */
    private $api;


    /**
     * International Standard Book Number
     *
     * @var string
     */
    protected $isbn;


    /**
     * Source data fetched from KNV's API
     *
     * @var array
     */
    protected $source;


    /**
     * Dataset properties
     */

    /**
     * Title
     *
     * @var string
     */
    protected $title;


    /**
     * Subtitle
     *
     * @var string
     */
    protected $subtitle;


    /**
     * Publisher
     *
     * @var array
     */
    protected $publisher;


    /**
     * Description
     *
     * @var array
     */
    protected $description;


    /**
     * Retail price (in €)
     *
     * @var string
     */
    protected $retailPrice;


    /**
     * Release year
     *
     * @var string
     */
    protected $releaseYear;


    /**
     * Minimum age recommendation (in years)
     *
     * @var string
     */
    protected $age;


    /**
     * All series & volumes
     *
     * @var array
     */
    protected $series;


    /**
     * Weight (in g)
     *
     * @var string
     */
    protected $weight;


    /**
     * Dimensions (in cm)
     *
     * @var string
     */
    protected $dimensions;


    /**
     * Available languages
     *
     * @var array
     */
    protected $languages;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        # Store source data
        $this->source = $source;

        # Store API proxy
        $this->api = $props['api'];

        # Store valid ISBN
        $this->isbn = $props['identifier'];

        # Store product type
        $this->type = $props['type'];

        # Extract tags & involved people early on
        $this->tags         = $this->separateTags();
        $this->people       = $this->separatePeople();

        # Build basic dataset
        $this->title        = $this->buildTitle();
        $this->subtitle     = $this->buildSubtitle();
        $this->publisher    = $this->buildPublisher();
        $this->description  = $this->buildDescription();
        $this->retailPrice  = $this->buildretailPrice();
        $this->releaseYear  = $this->buildreleaseYear();
        $this->age          = $this->buildAge();
        $this->series       = $this->buildSeries();
        $this->weight       = $this->buildWeight();
        $this->dimensions   = $this->buildDimensions();
        $this->languages    = $this->buildLanguages();

        # Build categories & topics from tags
        $this->categories   = $this->buildCategories();
        $this->topics       = $this->buildTopics();

        # Set OLA code & message
        $this->olaCode    = $this->buildOlaCode();
        $this->olaMessage = $this->buildOlaMessage();
    }


    /**
     * Magic methods
     */

    /**
     * Export author & title when echoing object
     *
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->author)) {
            return $this->title();
        }

        return $this->author(true) . ': ' . $this->title();
    }


    /**
     * Methods
     */

    /**
     * Shows source data fetched from KNV's API
     *
     * @return array
     */
    public function showSource(): array
    {
        return $this->source;
    }


    /**
     * Checks whether source data was fetched from cache
     *
     * @return bool
     */
    public function fromCache(): bool
    {
        return $this->fromCache;
    }


    /**
     * Checks whether product has a predecessor
     *
     * @return bool
     */
    public function hasDowngrade(): bool
    {
        return isset($this->source['VorherigeAuflageGtin']);
    }


    /**
     * Loads & returns predecessor
     *
     * @return self
     */
    public function downgrade()
    {
        if (!isset($this->source['VorherigeAuflageGtin'])) {
            return $this;
        }

        return $this->api->load($this->source['VorherigeAuflageGtin']);
    }


    /**
     * Checks whether product has a successor
     *
     * @return bool
     */
    public function hasUpgrade(): bool
    {
        return isset($this->source['NeueAuflageGtin']);
    }


    /**
     * Loads & returns successor
     *
     * @return self
     */
    public function upgrade()
    {
        if (!isset($this->source['NeueAuflageGtin'])) {
            return $this;
        }

        return $this->api->load($this->source['NeueAuflageGtin']);
    }


    /**
     * Exports OLA record
     *
     * @param int $quantity Number of products to be delivered
     * @return \Fundevogel\Pcbis\Api\Ola
     */
    public function ola(int $quantity = 1): Ola
    {
        return $this->api->ola($this->isbn, $quantity);
    }


    /**
     * Exports ISBN
     *
     * @return string
     */
    public function isbn(): string
    {
        return $this->isbn;
    }


    /**
     * Dataset methods
     */

    /**
     * Builds title
     *
     * @return string
     */
    protected function buildTitle(): string
    {
        if (!isset($this->source['Titel'])) {
            if (isset($this->source['AutorSachtitel'])) {
                return $this->source['AutorSachtitel'];
            }

            return '';
        }

        return $this->source['Titel'];
    }


    /**
     * Exports title
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }


    /**
     * Builds subtitle
     *
     * @return string
     */
    protected function buildSubtitle(): string
    {
        if (!isset($this->source['Utitel']) || is_null($this->source['Utitel']) == null) {
            return '';
        }

        return $this->source['Utitel'];
    }


    /**
     * Exports subtitle
     *
     * @return string
     */
    public function subtitle(): string
    {
        return $this->subtitle;
    }


    /**
     * Builds publisher
     *
     * @return array
     */
    protected function buildPublisher(): array
    {
        if (!isset($this->source['IndexVerlag'])) {
            return [];
        }

        if (is_array($this->source['IndexVerlag'])) {
            $publisher = [];

            foreach ($this->source['IndexVerlag'] as $string) {
                # Skip variations
                if (Str::contains($string, ' # ')) {
                    continue;
                }

                $publisher[] = trim($string);
            }

            return $publisher;
        }

        return (array)trim($this->source['IndexVerlag']);
    }


    /**
     * Exports publisher(s)
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return array|string
     */
    public function publisher(bool $asArray = false): array|string
    {
        if (empty($this->publisher)) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return $this->publisher;
        }

        return A::first($this->publisher);
    }


    /**
     * Builds description(s)
     *
     * @return array
     */
    protected function buildDescription(): array
    {
        if (!isset($this->source['Text1'])) {
            return [];
        }

        # Prepare text for HTML processing
        # (1) Avoid `htmlParseStartTag: invalid element name in Entity` warnings
        # Sometimes, KNV uses '>>' & '<<' instead of quotation marks, leading to broken texts
        # See 978-3-8373-9003-2
        $text = Str::replace($this->source['Text1'], ['&gt;&gt;', '&lt;&lt;'], ['"', '"']);

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
     * Exports description
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return array|string
     */
    public function description(bool $asArray = false): array|string
    {
        if (empty($this->description)) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return $this->description;
        }

        return A::first($this->description);
    }


    /**
     * Builds retail price (in €)
     *
     * @return string
     */
    protected function buildRetailPrice(): string
    {
        // Input: XX(.YY)
        // Output: XX,YY
        if (!isset($this->source['PreisEurD'])) {
            return '';
        }

        $retailPrice = (float) $this->source['PreisEurD'];

        return number_format($retailPrice, 2, ',', '');
    }


    /**
     * Exports retail price
     *
     * @return string
     */
    public function retailPrice(): string
    {
        return $this->retailPrice;
    }


    /**
     * Builds release year
     *
     * @return string
     */
    protected function buildReleaseYear(): string
    {
        if (!isset($this->source['Erschjahr'])) {
            return '';
        }

        return $this->source['Erschjahr'];
    }


    public function releaseYear(): string
    {
        return $this->releaseYear;
    }


    /**
     * Builds minimum age recommendation (in years)
     * TODO: Cater for months
     *
     * @return string
     */
    protected function buildAge(): string
    {
        if (!isset($this->source['Alter'])) {
            return '';
        }

        $age = Str::substr($this->source['Alter'], 0, 2);

        if (Str::substr($age, 0, 1) === '0') {
            $age = Str::substr($age, 1, 1);
        }

      	return 'ab ' . $age . ' Jahren';
    }


    /**
     * Exports age recommendation
     *
     * @return string
     */
    public function age(): string
    {
        return $this->age;
    }


    /**
     * Builds series
     *
     * @return array
     */
    protected function buildSeries(): array
    {
        $array = [
            'VerwieseneReihe1' => 'BandnrVerwieseneReihe1',
            'VerwieseneReihe2' => 'BandnrVerwieseneReihe2',
            'VerwieseneReihe3' => 'BandnrVerwieseneReihe3',
            'VerwieseneReihe4' => 'BandnrVerwieseneReihe4',
            'VerwieseneReihe5' => 'BandnrVerwieseneReihe5',
            'VerwieseneReihe6' => 'BandnrVerwieseneReihe6',
        ];

        $series = [];

        foreach ($array as $key => $value) {
            if (isset($this->source[$key]) && isset($this->source[$value])) {
                $series[trim($this->source[$key])] = trim($this->source[$value]);
            }
        }

        return $series;
    }


    /**
     * Whether product is part of one (or more) series
     *
     * @return bool
     */
    public function isSeries(): bool
    {
        return empty($this->series) === false;
    }


    /**
     * Exports series
     *
     * @return string
     */
    public function series(): string
    {
        if (empty($this->series)) {
            return '';
        }

        return A::first(array_keys($this->series));
    }


    /**
     * Exports volume
     *
     * @return string
     */
    public function volume(): string
    {
        if (empty($this->series)) {
            return '';
        }

        return A::first(array_values($this->series));
    }


    /**
     * Exports all series & volumes
     *
     * @return array
     */
    public function allSeries(): array
    {
        if (empty($this->series)) {
            return [];
        }

        return $this->series;
    }


    /**
     * Builds weight (in g)
     *
     * @return string
     */
    protected function buildWeight(): string
    {
        if (!isset($this->source['Gewicht'])) {
            return '';
        }

        return $this->source['Gewicht'];
    }


    /**
     * Exports weight
     *
     * @return string
     */
    public function weight(): string
    {
        return $this->weight;
    }


    /**
     * Exports height (in cm)
     *
     * @return string
     */
    public function height(): string
    {
        if (!isset($this->source['Höhe'])) {
            return '';
        }

        return Butler::convertMM($this->source['Höhe']);
    }


    /**
     * Exports width (in cm)
     *
     * @return string
     */
    public function width(): string
    {
        if (!isset($this->source['Breite'])) {
            return '';
        }

        return Butler::convertMM($this->source['Breite']);
    }


    /**
     * Exports depth (in cm)
     *
     * @return string
     */
    public function depth(): string
    {
        if (!isset($this->source['Tiefe'])) {
            return '';
        }

        return Butler::convertMM($this->source['Tiefe']);
    }


    /**
     * Builds dimensions (in cm)
     *
     * Examples:
     * - height / width
     * - height x width
     * - height x width x depth
     */
    protected function buildDimensions(): string
    {
        return A::join(array_filter([
            $this->height(),
            $this->width(),
            $this->depth(),
        ]), 'x');
    }


    /**
     * Exports dimensions
     *
     * @return string
     */
    public function dimensions(): string
    {
        return $this->dimensions;
    }


    /**
     * Builds languages
     *
     * @return array
     */
    protected function buildLanguages(): array
    {
        if (!isset($this->source['Sprachschl'])) {
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

        if (is_array($this->source['Sprachschl'])) {
            return array_map(function(string $languageCode) use ($languageCodes) {
                # Be safe, trim strings
                return $languageCodes[trim($languageCode)];
            }, $this->source['Sprachschl']);
        }

        # Be safe, trim strings
        return (array)$languageCodes[trim($this->source['Sprachschl'])];
    }


    /**
     * Exports language(s)
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return string|array
     */
    public function languages(bool $asArray = false)
    {
        if (empty($this->languages)) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return $this->languages;
        }

        return A::join($this->languages, ', ');
    }


    /**
     * Builds OLA code
     *
     * @return string
     */
    protected function buildOlaCode(): string
    {
        if (isset($this->source['Mnr'])) {
            return $this->source['Mnr'];
        }

        return '';
    }


    /**
     * Builds OLA message
     *
     * @return string
     */
    protected function buildOlaMessage(): string
    {
        if (array_key_exists($this->olaCode, $this->olaMessages)) {
            return $this->olaMessages[$this->olaCode];
        }

        return '';
    }


    /**
     * Checks if product is available / may be purchased
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->available);
        }

        return $this->ola()->isAvailable();
    }


    /**
     * Checks if product is permanently unavailable
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->unavailable);
        }

        return !$this->isAvailable();
    }


    /**
     * Exports type of value added tax (VAT)
     *
     * '0' = zero
     * '1' = half
     * '2' = full
     *
     * @return string
     */
    public function vat(): string
    {
        if (!isset($this->source['Mwstknz'])) {
            return '';
        }

        return $this->source['Mwstknz'];
    }


    /**
     * Exports all data
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return [
            # (1) Base
            'Titel'               => $this->title(),
            'Untertitel'          => $this->subtitle(),
            'Verlag'              => $this->publisher(),
            'Inhaltsbeschreibung' => $this->description($asArray),
            'Preis'               => $this->retailPrice(),
            'Erscheinungsjahr'    => $this->releaseYear(),
            'Altersempfehlung'    => $this->age(),
            'Reihe'               => $this->series(),
            'Band'                => $this->volume(),
            'Gewicht'             => $this->weight(),
            'Abmessungen'         => $this->dimensions(),
            'Sprachen'            => $this->languages($asArray),

            # (2) Extension 'Tags'
            'Kategorien'          => $this->categories($asArray),
            'Themen'              => $this->topics($asArray),
        ];
    }
}
