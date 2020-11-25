<?php

namespace PHPCBIS\Products\Books;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Product;
use PHPCBIS\Traits\DownloadCover;


/**
 * Class Book
 *
 * @package PHPCBIS
 */

class Book extends Product
{
    /**
     * Traits
     */

    use DownloadCover;


    /**
     * Properties
     */

    /**
     * Publisher
     *
     * @var string
     */
    protected $publisher;


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
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Extend dataset
        $this->publisher    = $this->buildPublisher();
        $this->binding      = $this->buildBinding();
        $this->pageCount    = $this->buildPageCount();
        $this->dimensions   = $this->buildDimensions();

        # Build involved people
        $this->illustrator  = $this->getRole('illustrator', true);
        $this->drawer       = $this->getRole('drawer', true);
        $this->photographer = $this->getRole('photographer', true);
        $this->translator   = $this->getRole('translator', true);
        $this->editor       = $this->getRole('editor', true);
        $this->participant  = $this->getRole('participant', true);
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


    /**
     * Overrides
     */

    /**
     * Builds categories
     *
     * @return array
     */
    protected function buildCategories(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        $categories = [];

        foreach ($this->tags as $tag) {
            $lowercase = Butler::lower($tag);

            if (Butler::contains($lowercase, 'bilderbuch')) {
                $categories[] = 'Bilderbuch';
            }

            if (Butler::contains($lowercase, 'vorlesebuch')) {
                $categories[] = 'Vorlesebuch';
            }

            if (Butler::contains($lowercase, 'sachbuch')) {
                $categories[] = 'Sachbuch';
            }
        }

        return array_unique($categories);
    }


    /**
     * Methods
     */

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
