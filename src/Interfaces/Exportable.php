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
 * Interface Exportable
 *
 * Defines requirements for exporting a complete dataset
 */
interface Exportable
{
    /**
     * Exports dataset
     */
    public function export(bool $asArray = false);
}
