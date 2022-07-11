<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Fields\Types;

use Fundevogel\Pcbis\Classes\Product\Fields\Value;
use Fundevogel\Pcbis\Helpers\A;

/**
 * Class Series
 *
 * Holds series & volumes
 */
class Series extends Value
{
    /**
     * Magic methods
     */

    /**
     * Casts data to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }


    /**
     * Dataset methods
     */

    /**
     * Exports all series
     *
     * @return array
     */
    public function series(): array
    {
        return is_null($this->data) ? [] : array_keys($this->data);
    }


    /**
     * Exports all volumes
     *
     * @return array
     */
    public function volumes(): array
    {
        return is_null($this->data) ? [] : array_values($this->data);
    }


    /**
     * Methods
     */

    /**
     * Converts data to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return is_null($this->data) ? [] : $this->data;
    }


    /**
     * Converts data to JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->data);
    }


    /**
     * Converts data to string
     *
     * @param string $delimiter Separator
     * @return string
     */
    public function toString(string $delimiter = '<br \>'): string
    {
        return is_null($this->data) ? '' : A::join(array_keys($this->data), $delimiter);
    }
}
