<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

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
     * Overrides
     */

    /**
     * Exports all data
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Medium' dataset
            parent::export($asArray),
            [
                # (2) 'Audiobook' specific data
                'ErzählerIn' => $this->getRole('narrator', $asArray),
            ]
        );
    }
}
