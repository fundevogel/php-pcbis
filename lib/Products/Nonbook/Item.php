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
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return [
            'Titel'               => $this->title(),
            'Untertitel'          => $this->subtitle(),
            'Inhaltsbeschreibung' => $this->description($asArray),
            'Preis'               => $this->retailPrice(),
            'Erscheinungsjahr'    => $this->releaseYear(),
            'Altersempfehlung'    => $this->age(),
        ];
    }
}
