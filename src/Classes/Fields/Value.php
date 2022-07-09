<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Fields;

use Fundevogel\Pcbis\Helpers\A;

/**
 * Class Value
 *
 * Template for all field values
 */
abstract class Value
{
    /**
     * Constructor
     *
     * @param array $data Roles, each holding involved people thereof
     * @return void
     */
    public function __construct(public array $data = [])
    {
    }


    /**
     * Magic methods
     */

    /**
     * Prints roles & involved people when casting to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }


    /**
     * Methods
     */

    /**
     * Converts data to string
     *
     * @return string
     */
    abstract public function toString(): string;


    /**
     * Converts data to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * Converts data to JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
