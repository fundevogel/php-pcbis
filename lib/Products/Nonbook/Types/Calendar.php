<?php

namespace Pcbis\Products\Nonbook\Types;

use Pcbis\Products\Nonbook\Item;

use Pcbis\Traits\Shared\Dimensions;
use Pcbis\Traits\Shared\Publisher;


/**
 * Class Calendar
 *
 * KNV product category 'Kalender'
 *
 * @package PHPCBIS
 */

class Calendar extends Item {
    /**
     * Traits
     */

    use Dimensions;
    use Publisher;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->publisher  = $this->buildPublisher();
        $this->dimensions = $this->buildDimensions();
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
            # (2) 'Calendar' specific data
            'Verlag'      => $this->publisher(),
            'Abmessungen' => $this->dimensions(),
        ]);
    }
}
