<?php

namespace Pcbis\Products\Media\Types;

use Pcbis\Products\Media\Medium;


/**
 * Class Audiobook
 *
 * KNV product category 'Hörbuch'
 *
 * @package PHPCBIS
 */

class Audiobook extends Medium {
    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->series    = $this->buildSeries();
        $this->volume    = $this->buildVolume();
    }


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
        return array_merge(
            # (1) 'Media' dataset
            parent::export($asArray), [
            # (2) 'Audiobook' specific data
            'ErzählerIn' => $this->getRole('narrator', $asArray),
            'Reihe'      => $this->series(),
            'Band'       => $this->volume(),
        ]);
    }
}
