<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
use Fundevogel\Pcbis\Classes\Product\Fields\Types\Series;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Interfaces\Field;
use Fundevogel\Pcbis\Traits\OlaStatus;
use Fundevogel\Pcbis\Traits\People;
use Fundevogel\Pcbis\Traits\Tags;
use Fundevogel\Pcbis\Utilities\Butler;

use DOMDocument;

/**
 * Class Product
 *
 * Base class of all product types
 */
class Product extends ProductBase
{
    /**
     * Traits
     */

    use OlaStatus;
    use People;


    /**
     * Properties
     */

    /**
     * Product EAN/ISBN
     *
     * @var string
     */
    protected string $identifier;


    /**
     * Constructor
     *
     * @param array $data Source data as fetched from KNV's API
     * @param \Fundevogel\Pcbis\Api\Webservice $api Object granting access to KNV's API
     * @return void
     */
    public function __construct(array $data, ?Webservice $api = null)
    {
        # Execute default constructor
        parent::__construct($data, $api);

        # Store product EAN/ISBN
        $this->identifier = $this->data['EAN'];

        # Process & add involved people
        $this->people = $this->setUpPeople();
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
            return sprintf('%s: %s', $author->toString(', '), $this->title()->toString());
        }

        # .. otherwise, only product title
        return $this->title()->toString();
    }


    /**
     * Methods
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
     * Exports International Standard Book Number (ISBN)
     *
     * @return string
     */
    public function isbn(): string
    {
        # If present ..
        if (class_exists('Nicebooks\Isbn\Isbn')) {
            # .. attempt to ..
            try {
                # .. format product EAN/ISBN using third-party tools
                return \Nicebooks\Isbn\Isbn::of($this->identifier)->format();
            } catch (\Exception $e) {
            }
        }

        return $this->identifier;
    }


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
     * Exports title
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function title(): Value
    {
        return new Value($this->data['Titel'] ?? $this->data['AutorSachtitel'] ?? '');
    }


    /**
     * Exports subtitle
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function subtitle(): Value
    {
        return new Value($this->data['Utitel'] ?? '');
    }


    /**
     * Exports publisher(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function publisher(): Value
    {
        if (!isset($this->data['IndexVerlag'])) {
            return new Value();
        }

        if (is_string($this->data['IndexVerlag'])) {
            return new Value(trim($this->data['IndexVerlag']));
        }

        $publisher = [];

        foreach ($this->data['IndexVerlag'] as $string) {
            # Skip variations
            if (Str::contains($string, ' # ')) {
                continue;
            }

            $publisher[] = trim($string);
        }

        return new Value($publisher);
    }


    /**
     * Exports description(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function description(): Value
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

            # Iterate over `span` elements
            foreach ($dom->getElementsByTagName('span') as $node) {
                # Skip empty nodes
                if (is_null($node->nodeValue)) {
                    continue;
                }

                # Format their text by ..
                # (1) .. decoding them as UTF-8
                # (2) .. removing unnecessary whitespaces
                $description[] = trim(utf8_decode($node->nodeValue));
            }
        }

        return new Value($description);
    }


    /**
     * Exports retail price (in €)
     *
     * Examples:
     * - XX    => XX,00
     * - XX.YY => XX,YY
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function retailPrice(): Value
    {
        if (!isset($this->data['PreisEurD'])) {
            return new Value();
        }

        return new Value(number_format((float) $this->data['PreisEurD'], 2, ',', ''));
    }


    /**
     * Exports release year
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function releaseYear(): Value
    {
        return new Value($this->data['Erschjahr'] ?? '');
    }


    /**
     * Exports recommended minimum age (in years)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function age(): Value
    {
        if (!isset($this->data['Alter'])) {
            return new Value();
        }

        $age = Str::substr($this->data['Alter'], 0, 2);

        if (Str::substr($age, 0, 1) === '0') {
            $age = Str::substr($age, 1, 1);
        }

        # TODO: Add support for months
        return new Value('ab ' . $age . ' Jahren');
    }


    /**
     * Exports series & volume(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Types\Series
     */
    public function series(): Series
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
                $array[trim($this->data[$series])] = null;

                # If volume is also present ..
                if (isset($this->data[$volume])) {
                    # .. add it to its series
                    $array[trim($this->data[$series])] = trim($this->data[$volume]);
                }
            }
        }

        return new Series($array);
    }


    /**
     * Checks whether product is part of (at least one) series
     *
     * @return bool
     */
    public function isSeries(): bool
    {
        return isset($this->data['VerwieseneReihe1']);
    }


    /**
     * Exports weight (in g)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function weight(): Value
    {
        # TODO: Always grams?
        return new Value($this->data['Gewicht'] ?? '');
    }


    /**
     * Exports width (in cm)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function width(): Value
    {
        if (!isset($this->data['Breite'])) {
            return new Value();
        }

        return new Value($this->convertMM($this->data['Breite']));
    }


    /**
     * Exports height (in cm)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function height(): Value
    {
        if (!isset($this->data['Höhe'])) {
            return new Value();
        }

        return new Value($this->convertMM($this->data['Höhe']));
    }


    /**
     * Exports depth (in cm)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function depth(): Value
    {
        if (!isset($this->data['Tiefe'])) {
            return new Value();
        }

        return new Value($this->convertMM($this->data['Tiefe']));
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
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function dimensions(): Value
    {
        return new Value(A::join(array_filter([
            $this->width()->toString(),
            $this->height()->toString(),
            $this->depth()->toString(),
        ]), 'x'));
    }


    /**
     * Exports language(s)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function languages(): Value
    {
        if (!isset($this->data['Sprachschl'])) {
            return new Value();
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
            return new Value(array_map(function (string $languageCode) use ($languageCodes) {
                # Be safe, trim strings
                return $languageCodes[trim($languageCode)];
            }, $this->data['Sprachschl']));
        }

        # Be safe, trim strings
        return new Value($languageCodes[trim($this->data['Sprachschl'])]);
    }


    /**
     * Exports type of value added tax (VAT)
     *
     * Examples:
     * - '0' = none
     * - '1' = half
     * - '2' = full
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function vat(): Value
    {
        if (!isset($this->data['Mwstknz'])) {
            return new Value();
        }

        $vatCodes = [
            '0' => 'kein',
            '1' => 'halb',
            '2' => 'voll',
        ];

        return new Value($vatCodes[$this->data['Mwstknz']]);
    }


    /**
     * Extracts tags from raw data
     *
     * @return array
     */
    protected function getTags(): array
    {
        if (!isset($this->data['IndexSchlagw'])) {
            return [];
        }

        if (is_string($this->data['IndexSchlagw'])) {
            return (array) trim($this->data['IndexSchlagw']);
        }

        return $this->data['IndexSchlagw'];
    }


    /**
     * Exports categories
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function categories(): Value
    {
        # Create data array
        $categories = [];

        # Fail early for empty values
        if (empty($tags = $this->getTags())) {
            return new Value($categories);
        }

        # Low-hanging fruit
        # (1) Audiobooks
        if ($this->isAudiobook()) {
            $categories[] = 'Hörbuch';
        }

        # (2) Schoolbooks
        if ($this->isSchoolbook()) {
            $categories[] = 'Schulbuch';
        }

        foreach ($tags as $tag) {
            # Be safe, trim whitespaces
            $tag = trim($tag);

            # Define exact example values
            $higher = [
                'Bilderbuch',
                'Kinderbuch',
                'Jugendbuch',
                'Sachbuch',
            ];

            # High(er) accuracy
            foreach ($higher as $example) {
                # If strings match (case-insensitive) ..
                if (Str::contains($tag, $example, true)) {
                    # .. add them
                    $categories[] = $example;
                }
            }

            # Define vague example values
            $lower = [
                'erstlese'    => 'Erstlesebuch',
                'sachbilder'  => 'Sachbuch',
                'sach-bilder' => 'Sachbuch',
                'vorlese'     => 'Vorlesebuch',
            ];

            # Low(er) accuracy
            foreach ($lower as $key => $value) {
                # If strings match (case-insensitive) ..
                if (Str::contains($tag, $key, true)) {
                    # .. add them
                    $categories[] = $value;
                }
            }
        }

        return new Value(array_unique($categories));
    }


    /**
     * Exports topics
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function topics(): Value
    {
        # Create data array
        $topics = [];

        # Fail early for empty values
        if (empty($tags = $this->getTags())) {
            return new Value($topics);
        }

        foreach ($tags as $tag) {
            # Extract relevant string
            $string = A::last(Str::split($tag, ';'));

            # Filter out ..
            # (1) .. categories
            $categories = [
                'bilderbuch',
                'hörbuch',
                'jugendbuch',
                'kinderbuch',
                'sachbuch',
                'schulbuch',
            ];

            $skip = false;

            foreach ($categories as $category) {
                # Upon first hit ..
                if (Str::contains($string, $category, true)) {
                    $skip = true;

                    # .. abort execution
                    break;
                }
            }

            # (2) .. 'Antolin' rating
            if (Str::startsWith($string, 'Antolin')) {
                $skip = true;
            }

            # (3) Skip them
            if ($skip) {
                continue;
            }

            $topics[] = trim($string);
        }

        if ($this->data['EAN'] == 'harhar') {
            var_dump($topics);
        }

        return new Value(array_unique($topics));
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
            'Titel'               => $this->title()->value(),
            'Untertitel'          => $this->subtitle()->value(),
            'Verlag'              => $this->publisher()->value(),
            'Inhaltsbeschreibung' => $this->description()->value(),
            'Preis'               => $this->retailPrice()->value(),
            'Erscheinungsjahr'    => $this->releaseYear()->value(),
            'Altersempfehlung'    => $this->age()->value(),
            'Reihen'              => $this->series()->value(),
            'Gewicht'             => $this->weight()->value(),
            'Abmessungen'         => $this->dimensions()->value(),
            'Sprachen'            => $this->languages()->value(),
            'Mehrwehrtsteuersatz' => $this->vat()->value(),
            'Kategorien'          => $this->categories()->value(),
            'Themen'              => $this->topics()->value(),
        ];
    }


    /**
     * Helpers
     */

    /**
     * Converts millimeters to centimeters
     *
     * @param string $string Millimeter information
     * @return string
     */
    protected function convertMM(string $string): string
    {
        # TODO: Messing up some other values, needs fixing
        # Edge case: string already contains width/height in centimeters
        # See 978-3-7891-2946-9
        if (Str::contains($string, ',')) {
            return $string;
        }

        # Convert value
        $string = (float) $string / 10;

        return Str::replace((string) $string, '.', ',');
    }


    /**
     * Downloads cover images from the German National Library (DNB)
     *
     * @param mixed $file Path to download file OR file-like object
     * @param string $ua User-Agent used when downloading cover images
     * @return bool Download status
     */
    public function downloadCover(mixed $file = null, ?string $ua = null): bool
    {
        return Butler::downloadCover($this->ean(), $file, $ua);
    }
}
