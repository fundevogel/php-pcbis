<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Interfaces;

/**
 * Interface Products
 *
 * Defines implementation of product collections
 */
interface Products
{
    /**
     * Methods
     */

    /**
     * Counts products
     *
     * @return int
     */
    public function count(): int;
}
