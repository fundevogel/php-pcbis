<?php

namespace PHPCBIS\Products\Books;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Product;


/**
 * Class Book
 *
 * Holds information from KNV's API in a human-readable form &
 * downloads book covers from the German National Library
 *
 * @package PHPCBIS
 */

class Book extends Product
{
    /**
     * Properties
     */

    /**
     * Author
     *
     * @var array
     */
    protected $author;


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
     * @var string
     */
    protected $publisher;


    /**
     * Description
     *
     * @var string
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
     * Binding
     *
     * @var string
     */
    protected $binding;


    /**
     * Page count
     *
     * @var string
     */
    protected $pageCount;


    /**
     * Dimensions (width x height in centimeters)
     *
     * @var string
     */
    protected $dimensions;


    /**
     * Illustrator
     *
     * @var array
     */
    protected $illustrator;


    /**
     * Drawer
     *
     * @var array
     */
    protected $drawer;


    /**
     * Photographer
     *
     * @var array
     */
    protected $photographer;


    /**
     * Translator
     *
     * @var array
     */
    protected $translator;


    /**
     * Editor
     *
     * @var array
     */
    protected $editor;


    /**
     * Participant
     *
     * @var array
     */
    protected $participant;


    /**
     * Antolin rating (suitable grade)
     *
     * @var string
     */
    protected $antolin = '';


    /**
     * List of blocked topics
     *
     * @var array
     */
    protected $blockList = [
        # Rather categories than tags
        'Hörbuch',
        'Papp-Bilderbuch',
        'Umwelt-Bilderbuch',
        'Vorlesebuch',

        # Highly sophisticated ways to say 'book for kids'
        # (1) Non-fiction for kids
        'Kinder-/Jugendsachbuch',
        'Kindersachbuch/Jugendsachbuch',
        'Kindersachbuch/Jugendsachbuch.',
        # (2) Literature for children & adolescents
        'Kinderliteratur/Jugendliteratur',
        'Kinder-/Jugendliteratur',
        'Kinder/Jugendliteratur',
        'Kinder-/Jugendlit.',
        'Kinder/Jugendlit.',
    ];


    /**
     * User-Agent used when downloading book cover images
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Build dataset
        $this->author       = $this->buildAuthor();
        $this->title        = $this->buildTitle();
        $this->subtitle     = $this->buildSubtitle();
        $this->publisher    = $this->buildPublisher();
        $this->description  = $this->buildDescription();
        $this->retailPrice  = $this->buildretailPrice();
        $this->releaseYear  = $this->buildreleaseYear();
        $this->age          = $this->buildAge();
        $this->binding      = $this->buildBinding();
        $this->pageCount    = $this->buildPageCount();
        $this->dimensions   = $this->buildDimensions();

        # Build involved people
        $this->illustrator  = $this->getRole('illustrator', true);
        $this->drawer       = $this->getRole('drawer', true);
        $this->photographer = $this->getRole('photographer', true);
        $this->translator   = $this->getRole('translator', true);
        $this->editor       = $this->getRole('editor', true);
        $this->participant  = $this->getRole('participant');
    }


    /**
     * Magic methods
     */

    public function __toString(): string
    {
        if (empty($this->author)) {
            return $this->getTitle();
        }

        return $this->getAuthor(true) . ': ' . $this->getTitle();
    }


    /**
     * Setters & getters
     */

    public function setAntolin(string $antolin)
    {
        $this->antolin = $antolin;
    }

    public function getAntolin(): string
    {
        return $this->antolin;
    }

    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }


    /**
     * Methods
     */

    /**
     * Downloads book cover from DNB
     *
     * @param string $fileName - Filename for the image to be downloaded
     * @param bool $overwrite - Whether existing file should be overwritten
     * @return bool
     */
    public function downloadCover(string $fileName = null, bool $overwrite = false): bool
    {
        if ($fileName == null) {
            $fileName = $this->isbn;
        }

        $file = realpath($this->imagePath . '/' . $fileName . '.jpg');

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        if (file_exists($file) && !$overwrite) {
            return true;
        }

        $success = false;

        if ($handle = fopen($file, 'w')) {
            $client = new \GuzzleHttp\Client();
            $url = 'https://portal.dnb.de/opac/mvb/cover.htm?isbn=' . $this->isbn;

            try {
                $response = $client->get($url, ['sink' => $handle]);
                $success = true;
            } catch (\GuzzleHttp\Exception\ClientException $e) {}
        }

        return $success;
    }


    /**
     * Builds author(s)
     *
     * @return array
     */
    protected function buildAuthor(): array
    {
        if (!isset($this->source['AutorSachtitel'])) {
            return [];
        }

        $string = $this->source['AutorSachtitel'];

        $groupDelimiter = ';';
        $personDelimiter = ',';

        # Edge case: `AutorSachtitel` contains something other than a person
        if (!Butler::contains($string, $groupDelimiter) && !Butler::contains($string, $personDelimiter)) {
            if (!empty($this->people['original'])) {
                return $this->people['original'];
            }

            if (isset($this->source['IndexAutor']) && is_string($this->source['IndexAutor'])) {
                $string = trim($this->source['IndexAutor']);
            } else {
                return [];
            }
        }

        // $array = Butler::split($string, $groupDelimiter);
        // $authors = [];

        // foreach ($array as $author) {
        //     $group = Butler::split($author, $personDelimiter);

        //     $authors[] = $this->organizePeople($group);
        // }

        $authors = $this->organizePeople($string);

        return $authors;
    }


    public function author(bool $asArray = false)
    {
        if ($asArray) {
            return $this->author;
        }

        if (empty($this->author)) {
            return '';
        }

        $authors = [];

        foreach ($this->author as $author) {
            $authors[] = Butler::join($author, ' ');
        }

        return Butler::join($authors, '; ');
    }


    /**
     * Builds title
     *
     * @return string
     */
    protected function buildTitle(): string
    {
        if (!isset($this->source['Titel'])) {
            return '';
        }

        return $this->source['Titel'];
    }


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
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return '';
        }

        return $this->source['Utitel'];
    }


    public function subtitle(): string
    {
        return $this->subtitle;
    }


    /**
     * Builds publisher
     *
     * @return string
     */
    protected function buildPublisher(): string
    {
        if (!isset($this->source['IndexVerlag'])) {
            return '';
        }

        $publisher = $this->source['IndexVerlag'];

        if (is_array($publisher)) {
            $publisher = Butler::first($publisher);
        }

        return trim($publisher);
    }


    public function publisher(): string
    {
        return $this->publisher;
    }


    /**
     * Builds description
     *
     * @return string
     */
    protected function buildDescription(): string
    {
        if (!isset($array['Text1'])) {
            return '';
        }

        $string = $array['Text1'];
        $description = Butler::split($string, 'º');

        foreach ($description as $index => $text) {
            $text = htmlspecialchars_decode($text);
            $text = Butler::replace($text, '<br><br>', '. ');
            $text = Butler::unhtml($text);
            $description[$index] = $text;

            if (Butler::length($description[$index]) < 130 && count($description) > 1) {
                unset($description[array_search($text, $description)]);
            }
        }

        return Butler::first($description);
    }

    public function description(): string
    {
        return $this->description;
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

        $age = Butler::substr($this->source['Alter'], 0, 2);

        if (Butler::substr($age, 0, 1) === '0') {
            $age = Butler::substr($age, 1, 1);
        }

      	return 'ab ' . $age . ' Jahren';
    }

    public function getAge(): string
    {
        return $this->age;
    }


    /**
     * Builds binding
     *
     * @return string
     */
    protected function buildBinding(): string
    {
        if (!isset($this->source['Einband'])) {
            return '';
        }

        $binding = $this->source['Einband'];

        $translations = [
            'BUCH' => 'gebunden',
            'CRD'  => 'Nonbook',
            'GEB'  => 'gebunden',
            'GEH'  => 'geheftet',
            'HL'   => 'Halbleinen',
            'KT'   => 'kartoniert',
            'LN'   => 'Leinen',
            'NON'  => 'Nonbook',
            'PP'   => 'Pappband',
            'SPL'  => 'Spiel',
        ];

        if (!empty($this->translations)) {
            $translations = $this->translations;
        }

        if (!isset($translations[$binding])) {
            return $binding;
        }

        return $translations[$binding];
    }

    public function binding(): string
    {
        return $this->binding;
    }


    /**
     * Builds page count
     *
     * @return string
     */
    protected function buildPageCount(): string
    {
        if (!isset($this->source['Abb'])) {
            return '';
        }

        $string = $this->source['Abb'];
        $array = Butler::split($string, '.');

        foreach ($array as $line) {
            if (Butler::substr($line, -1) === 'S') {
                return Butler::split($line, ' ')[0];
            }
        }

        return '';
    }

    public function pageCount(): string
    {
        return $this->pageCount;
    }


    /**
     * Builds dimensions (width x height)
     *
     * @return string
     */
    protected function buildDimensions(): string
    {
        # Width & height are either both present, or not at all
        if (!isset($this->source['Breite'])) {
            $delimiter = ' cm';

            # If they aren't though, check 'Abb' for further hints on dimensions
            if (Butler::contains($this->source['Abb'], $delimiter)) {
                $string = Butler::replace($this->source['Abb'], $delimiter, '');
                $array = Butler::split($string, ' ');

                return Butler::convertMM(Butler::last($array));
            }

            return '';
        }

        $width = Butler::convertMM($this->source['Breite']);
        $height = Butler::convertMM($this->source['Hoehe']);

        return $width . ' x ' . $height;
    }

    public function setDimensions(string $dimensions)
    {
        $this->dimensions = $dimensions;
    }

    public function getDimensions(): string
    {
        return $this->dimensions;
    }


    /**
     * Builds topics
     *
     * @return array
     */
    protected function buildTopics(): array
    {
        $tags = parent::buildTopics();

        $translations = [
            'Auto / Personenwagen / Pkw' => 'Autos',
            'Coming of Age / Erwachsenwerden' => 'Erwachsenwerden',
            'Demenz / Alzheimersche Krankheit' => 'Demenz',
            'Deutsche Demokratische Republik (DDR)' => 'DDR',
            'Flucht / Flüchtling' => 'Flucht',
            'IM (Staatssicherheitsdienst)' => 'Inoffizielle MitarbeiterInnen',
            'Klassenfahrt / Schulfahrt' => 'Klassenfahrt',
            'Klassiker (Literatur)' => 'Klassiker',
            'Klimaschutz, Klimawandel / Klimaveränderung' => 'Klimaschutz',
            'Klimawandel / Klimaveränderung' => 'Klimawandel',
            'Krebs (Krankheit) / Karzinom' => 'Krebserkrankung',
            'Leichte Sprache / Einfache Sprache' => 'Einfache Sprache',
            'Migration / Migrant' => 'Migration',
            'Regenwald / Dschungel' => 'Regenwald',
            'Schulanfang / Schulbeginn' => 'Schulanfang',
            'Selbstmord / Suizid / Freitod / Selbsttötung' => 'Selbsttötung',
            'Ski / Schi' => 'Skifahren',
            'Soziales Netzwerk (Internet) / Social Networking' => 'Social Media',
            'Spionage / Agent / Agentin / Spion / Spionin' => 'GeheimagentIn',
            'Staatssicherheitsdienst (Stasi)' => 'Stasi',
            'Traum / Träumen / Traumdeutung / Traumanalyse' => 'Traum',
            'Wolf / Wölfe (Tier)' => 'Wölfe',
        ];

        if (!empty($this->translations)) {
            $translations = $this->translations;
        }

        $topics = array_map(function ($topic) use ($translations) {
            # Add 'Antolin' rating if available ..
            if (Butler::startsWith($topic, 'Antolin')) {
                $string = Butler::replace($topic, ['(', ')'], '');

                # .. but not as topic
                $this->antolin = Butler::split($string, 'Antolin')[0];

                return '';
            }

            if (isset($translations[$topic])) {
                return $translations[$topic];
            }
        }, $tags);

        return array_filter($topics);
    }
}
