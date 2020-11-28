<?php

namespace PHPCBIS\Products\Media;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Product;
use PHPCBIS\Traits\DownloadCover;


/**
 * Class Medium
 *
 * @package PHPCBIS
 */

class Medium extends Product
{
    /**
     * Traits
     */

    use DownloadCover;


    /**
     * Properties
     */

    /**
     * Director
     *
     * @var array
     */
    protected $director;


    /**
     * Producer
     *
     * @var array
     */
    protected $producer;


    /**
     * Duration (in minutes)
     *
     * @var string
     */
    protected $duration;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Extend dataset
        $this->duration = $this->buildDuration();

        # Build involved people
        $this->narrator = $this->getRole('narrator', true);
        $this->director = $this->getRole('director', true);
        $this->producer = $this->getRole('producer', true);
    }


    /**
     * Methods
     */

    /**
     * Builds duration
     *
     * @return string
     */
    protected function buildDuration(): string
    {
        if (!isset($this->source['Utitel'])) {
            return '';
        }

        $array = Butler::split($this->source['Utitel'], '.');

        return Butler::replace(Butler::last($array), ' Min', '');
    }


    public function duration(): string
    {
        return $this->duration;
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(bool $asArray = false): array {
        # Build dataset
        return [
            # (1) Base
            'Titel'               => $this->title(),
            'Untertitel'          => $this->subtitle(),
            'Inhaltsbeschreibung' => $this->description(),
            'Preis'               => $this->retailPrice(),
            'Erscheinungsjahr'    => $this->releaseYear(),
            'Altersempfehlung'    => $this->age(),

            # (2) Extension 'People'
            'AutorIn'             => $this->author($asArray),
            'IllustratorIn'       => $this->getRole('illustrator', $asArray),
            'ZeichnerIn'          => $this->getRole('drawer', $asArray),
            'PhotographIn'        => $this->getRole('photographer', $asArray),
            'ÜbersetzerIn'        => $this->getRole('translator', $asArray),
            'HerausgeberIn'       => $this->getRole('editor', $asArray),
            'MitarbeiterIn'       => $this->getRole('participant', $asArray),

            # (3) Extension 'Tags'
            'Kategorien'          => $this->categories($asArray),
            'Themen'              => $this->topics($asArray),

            # (4) 'Media' specific data
            'Dauer'               => $this->duration(),
            'KomponistIn'         => $this->getRole('composer', $asArray),
            'RegisseurIn'         => $this->getRole('director', $asArray),
            'ProduzentIn'         => $this->getRole('producer', $asArray),
        ];
    }
}
