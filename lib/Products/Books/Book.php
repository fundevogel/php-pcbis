<?php

namespace Pcbis\Products\Books;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Product;
use Pcbis\Traits\DownloadCover;

use Pcbis\Traits\Shared\Categories;
use Pcbis\Traits\Shared\Dimensions;
use Pcbis\Traits\Shared\Series;
use Pcbis\Traits\Shared\Topics;


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

    use Categories, Topics;
    use DownloadCover;
    use Dimensions;
    use Series;


    /**
     * Properties
     */

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
     * Antolin rating (suitable grade)
     *
     * @var string
     */
    protected $antolin = '';


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->publisher    = $this->buildPublisher();
        $this->binding      = $this->buildBinding();
        $this->pageCount    = $this->buildPageCount();
        $this->dimensions   = $this->buildDimensions();
        $this->series       = $this->buildSeries();
        $this->volume       = $this->buildVolume();
        $this->antolin      = $this->buildAntolin();
    }


    /**
     * Methods
     */

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


    /**
     * Exports binding
     *
     * @return string
     */
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


    /**
     * Exports page count
     *
     * @return string
     */
    public function pageCount(): string
    {
        return $this->pageCount;
    }


    /**
     * Builds Antolin rating
     *
     * @return string
     */
    protected function buildAntolin(): string
    {
        if (empty($this->tags)) {
            return '';
        }

        foreach ($this->tags as $tag) {
            if (Butler::startsWith($tag, 'Antolin')) {
                return Butler::replace($tag, ['Antolin (', ')'], '');
            }
        }

        return '';
    }


    /**
     * Exports Antolin rating
     *
     * @return string
     */
    public function antolin(): string
    {
        return $this->antolin;
    }


    /**
     * Exports all data
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
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

            # (2) Extension 'People'
            'AutorIn'             => $this->getRole('author', $asArray),
            'IllustratorIn'       => $this->getRole('illustrator', $asArray),
            'ZeichnerIn'          => $this->getRole('drawer', $asArray),
            'PhotographIn'        => $this->getRole('photographer', $asArray),
            'ÜbersetzerIn'        => $this->getRole('translator', $asArray),
            'HerausgeberIn'       => $this->getRole('editor', $asArray),
            'MitarbeiterIn'       => $this->getRole('participant', $asArray),

            # (3) Extension 'Tags'
            'Kategorien'          => $this->categories($asArray),
            'Themen'              => $this->topics($asArray),

            # (4) 'Book' specific data
            'Reihe'               => $this->series(),
            'Band'                => $this->volume(),
            'Einband'             => $this->binding(),
            'Seitenzahl'          => $this->pageCount(),
            'Abmessungen'         => $this->dimensions(),
            'Antolin'             => $this->antolin(),
        ];
    }
}
