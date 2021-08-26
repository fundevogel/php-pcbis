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
            # (1) 'Medium' dataset
            parent::export($asArray), [
            # (2) 'Audiobook' specific data
            'ErzählerIn' => $this->getRole('narrator', $asArray),
        ]);
    }
}
