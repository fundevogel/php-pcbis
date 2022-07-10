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
 * Interface Field
 *
 * Defines implementation of field values
 */
interface Field
{
    /**
     * Methods
     */

    /**
     * Converts data to string
     *
     * @return string
     */
    public function toString(): string;


    /**
     * Converts data to array
     *
     * @return array
     */
    public function toArray(): array;


    /**
     * Converts data to JSON string
     *
     * @return string
     */
    public function toJson(): string;


    /**
     * Exports default value
     *
     * @return mixed
     */
    public function value(): mixed;
}
