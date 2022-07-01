<?php

namespace Fundevogel\Pcbis\Products\Media\Types;

use Fundevogel\Pcbis\Products\Media\Medium;

/**
 * Class Audiobook
 *
 * KNV product category 'Hörbuch'
 */
class Audiobook extends Medium
{
    /**
     * Dataset methods
     */

    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Audiobook' specific data
            'ErzählerIn' => $this->getRole('narrator'),
        ]);
    }
}
