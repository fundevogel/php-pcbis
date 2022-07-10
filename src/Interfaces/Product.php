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
 * Interface Product
 *
 * Defines implementation of single products
 */
interface Product
{
    /**
     * Methods
     */

    /**
     * Exports European Article Number (EAN)
     *
     * @return string
     */
    public function ean(): string;


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array;
}
