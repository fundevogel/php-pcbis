<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Fields\Types;

use Fundevogel\Pcbis\Classes\Product\Fields\Types\Role;

class RoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * @var array
     */
    private static $person = [
        [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ],
    ];


    /**
     * @var array
     */
    private static $people = [
        [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ],
        [
            'firstName' => 'Jane',
            'lastName' => 'Done',
        ],
    ];


    /**
     * Tests
     */

    public function testCast2String(): void
    {
        # Run function #1
        $result1 = new Role(self::$person);

        # Assert result #1
        $this->assertEquals($result1->__toString(), 'John Doe');

        # Run function #2
        $result2 = new Role(self::$people);

        # Assert result #2
        $this->assertEquals($result2->__toString(), 'John Doe; Jane Done');
    }


    public function testToString(): void
    {
        # Run function #1
        $result = new Role(self::$person);

        # Assert result #1
        $this->assertEquals($result->toString(), 'John Doe');

        # Run function #2
        $result = new Role(self::$people);

        # Assert result #2
        $this->assertEquals($result->toString(), 'John Doe; Jane Done');
    }


    public function testToArray(): void
    {
        # Run function #1
        $result = new Role(self::$person);

        # Assert result #1
        $this->assertEquals($result->toArray(), self::$person);

        # Run function #2
        $result = new Role(self::$people);

        # Assert result #2
        $this->assertEquals($result->toArray(), self::$people);
    }
}
