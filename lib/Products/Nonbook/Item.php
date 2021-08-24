<?php

namespace Pcbis\Products\Nonbook;

use Pcbis\Products\Product;
use Pcbis\Traits\DownloadCover;


/**
 * Class Item
 *
 * @package PHPCBIS
 */

class Item extends Product {
    /**
     * Traits
     */

    use DownloadCover;


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
        return [
            'Titel'               => $this->title(),
            'Untertitel'          => $this->subtitle(),
            'Verlag'              => $this->publisher(),
            'Inhaltsbeschreibung' => $this->description($asArray),
            'Preis'               => $this->retailPrice(),
            'Erscheinungsjahr'    => $this->releaseYear(),
            'Altersempfehlung'    => $this->age(),
            'Abmessungen'         => $this->dimensions(),
            'Sprachen'            => $this->languages($asArray),
        ];
    }
}
