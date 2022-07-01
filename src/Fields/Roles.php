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
 * Class Roles
 *
 * Holds involved people of all roles
 */
class Roles
{
    /**
     * Constructor
     *
     * @param array $roles Available roles
     * @param array $people Roles, each holding involved people thereof
     */
    public function __construct(public array $roles, public array $people)
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
     * Formats string of roles & people
     *
     * @param string $peopleDelimiter Delimiter between people of same role
     * @param string $groupDelimiter Delimiter between role groups
     * @return string
     */
    public function toString(string $peopleDelimiter = '; ', string $groupDelimiter = '. '): string
    {
        # Create data array
        $result = [];

        foreach ($this->people as $role => $people) {
            # Skip unused roles
            if (empty($people)) {
                continue;
            }

            # Skip author
            if ($role == 'author') {
                continue;
            }

            # Create 'Role' instance
            $obj = new Role($role, $people);

            # Format string, using role & people thereof
            $result[] = sprintf('%s: %s', $this->roles[$role], $obj->toString($peopleDelimiter));
        }

        return A::join($result, $groupDelimiter);
    }


    /**
     * Provides involved people of all roles
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->people;
    }
}
