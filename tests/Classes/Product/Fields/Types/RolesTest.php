<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Fields\Types;

use Fundevogel\Pcbis\Classes\Product\Fields\Types\Roles;

class RolesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * @var array
     */
    private static $people = [
        'illustrator' => [
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
        ],
        'participant' => [
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            [
                'firstName' => 'Jane',
                'lastName' => 'Done',
            ],
        ],
    ];


    /**
     * Tests
     */

    public function testCast2String(): void
    {
        # Run function
        $result = new Roles(self::$people);

        # Assert result
        $this->assertEquals($result->__toString(), 'Illustration: John Doe. Mitarbeit: John Doe; Jane Done');
    }


    public function testToString(): void
    {
        # Run function
        $result = new Roles(self::$people);

        # Assert result
        $this->assertEquals($result->toString(), 'Illustration: John Doe. Mitarbeit: John Doe; Jane Done');
    }


    public function testToArray(): void
    {
        # Run function
        $result = new Roles(self::$people);

        # Assert result
        $this->assertEquals($result->value(), self::$people);
    }
}
