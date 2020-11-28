<?php

namespace PHPCBIS\Products\Media\Types;

use PHPCBIS\Products\Media\Medium;

use PHPCBIS\Traits\Shared\Publisher;
use PHPCBIS\Traits\Shared\Series;


/**
 * Class Audiobook
 *
 * KNV product category 'Hörbuch'
 *
 * @package PHPCBIS
 */

class Audiobook extends Medium {
    /**
     * Traits
     */

    use Publisher;
    use Series;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Extend dataset
        $this->publisher = $this->buildPublisher();
        $this->series    = $this->buildSeries();
        $this->volume    = $this->buildVolume();
    }


    /**
     * Overrides
     */

    /**
     * Exports all data
     *
     * @return array
     */
    public function export(bool $asArray = false): array {
        # Build dataset
        return array_merge(
            # (1) 'Media' dataset
            parent::export($asArray), [
            # (2) 'Audiobook' specific data
            'Verlag'     => $this->publisher(),
            'Reihe'      => $this->series(),
            'Band'       => $this->volume(),
            'ErzählerIn' => $this->getRole('narrator', $asArray),
        ]);
    }
}
