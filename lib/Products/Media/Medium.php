<?php

namespace Pcbis\Products\Media;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Product;


/**
 * Class Medium
 *
 * @package PHPCBIS
 */

class Medium extends Product
{
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

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->duration = $this->buildDuration();
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
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array {
        return array_merge(
            # Build dataset
            parent::export($asArray), [
            # (1) 'Media' specific data
            'Dauer'         => $this->duration(),
            'KomponistIn'   => $this->getRole('composer', $asArray),
            'RegisseurIn'   => $this->getRole('director', $asArray),
            'ProduzentIn'   => $this->getRole('producer', $asArray),

            # (2) Extension 'People'
            'AutorIn'       => $this->getRole('author', $asArray),
            'Vorlage'       => $this->getRole('original', $asArray),
            'IllustratorIn' => $this->getRole('illustrator', $asArray),
            'ZeichnerIn'    => $this->getRole('drawer', $asArray),
            'PhotographIn'  => $this->getRole('photographer', $asArray),
            'ÜbersetzerIn'  => $this->getRole('translator', $asArray),
            'HerausgeberIn' => $this->getRole('editor', $asArray),
            'MitarbeiterIn' => $this->getRole('participant', $asArray),
        ]);
    }
}
