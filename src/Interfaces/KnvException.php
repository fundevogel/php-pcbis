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
 * Interface KnvException
 *
 * Defines implementation of KNV webservice exceptions
 */
interface KnvException
{
    /**
     * Methods
     */

    /**
     * Exports HTTP status
     *
     * @return string
     */
    public function getStatus(): string;


    /**
     * Exports detailed exception description
     *
     * @return string
     */
    public function getDescription(): string;
}
