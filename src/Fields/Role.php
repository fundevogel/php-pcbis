<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Fields;

use Fundevogel\Pcbis\Helpers\A;

/**
 * Class Role
 *
 * Holds involved people of the same role
 */
class Role
{
    /**
     * Constructor
     *
     * @param string $role Role of involved people
     * @param array $people People of same role
     */
    public function __construct(public array $people)
    {
    }


    /**
     * Magic methods
     */

    /**
     * Prints involved people when casting to string
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
     * Formats string of involved people
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
        }, array_values($this->people)), $this->delimiter);
    }


    /**
     * Provides involved people of same role
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->people;
    }
}
