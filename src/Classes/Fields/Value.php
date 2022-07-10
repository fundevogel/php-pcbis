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
use Fundevogel\Pcbis\Interfaces\Field;

/**
 * Class Value
 *
 * Base class for all field values
 */
class Value implements Field
{
    /**
     * Constructor
     *
     * @param mixed $data Field value
     * @return void
     */
    public function __construct(public mixed $data = null)
    {
    }


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
     * Methods
     */

    /**
     * Converts data to array
     *
     * @return array
     */
    public function toArray(): array
    {
        if (is_null($this->data)) {
            return [];
        }

        if (is_array($this->data)) {
            return $this->data;
        }

        return (array) $this->data;
    }


    /**
     * Converts data to JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        if (is_null($this->data)) {
            return json_encode('');
        }

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
        if (is_null($this->data)) {
            return '';
        }

        if (is_string($this->data)) {
            return $this->data;
        }

        return A::join($this->data, $delimiter);
    }


    /**
     * Exports default value
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->data;
    }
}
