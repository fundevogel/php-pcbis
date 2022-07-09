<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Fields\Types;

use Fundevogel\Pcbis\Classes\Fields\Value;
use Fundevogel\Pcbis\Helpers\A;

/**
 * Class Role
 *
 * Holds involved people of the same role
 */
class Role extends Value
{
    /**
     * Methods
     */

    /**
     * Converts data to string
     *
     * @param string $delimiter Delimiter between people
     * @return string
     */
    public function toString(string $delimiter = '; '): string
    {
        # Formats people nicely (as string)
        # (1) Iterate over them
        # (2) Join each first & last name
        # (3) Separate each person using delimiter
        return A::join(array_map(function (array $person): string {
            return A::join($person, ' ');
        }, array_values($this->data)), $delimiter);
    }


    /**
     * Checks whether role is vacant
     *
     * @return bool
     */
    public function exists(): bool
    {
        return !empty($this->data);
    }
}
