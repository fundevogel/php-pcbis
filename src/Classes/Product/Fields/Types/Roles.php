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
 * Class Roles
 *
 * Holds involved people of all roles
 */
class Roles extends Value
{
    /**
     * Properties
     */

    /**
     * Types of involvement
     *
     * @var array
     */
    protected array $roles = [
        'author'       => 'Autorenschaft',
        'original'     => 'Vorlage',
        'illustrator'  => 'Illustration',
        'drawer'       => 'Zeichnungen',
        'photographer' => 'Fotos',
        'translator'   => 'Übersetzung',
        'narrator'     => 'Gesprochen',
        'composer'     => 'Komposition',
        'director'     => 'Regie',
        'producer'     => 'Produktion',
        'actor'        => 'Besetzung',
        'participant'  => 'Mitarbeit',
    ];


    /**
     * Methods
     */

    /**
     * Converts data to string
     *
     * @param string $delimiter Delimiter between people of same role
     * @param string $groupDelimiter Delimiter between role groups
     * @return string
     */
    public function toString(string $delimiter = '; ', string $groupDelimiter = '. '): string
    {
        # Create data array
        $result = [];

        foreach ($this->data as $role => $people) {
            # Skip unused roles
            if (empty($people)) {
                continue;
            }

            # Skip author
            if ($role == 'author') {
                continue;
            }

            # Create 'Role' instance
            $obj = new Role($people);

            # Format string, using role & people thereof
            $result[] = sprintf('%s: %s', $this->roles[$role], $obj->toString($delimiter));
        }

        return A::join($result, $groupDelimiter);
    }
}
