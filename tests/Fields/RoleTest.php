<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis\Tests\Fields;

use Fundevogel\Pcbis\Fields\Role;

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

    public function setUp(): void
    {
        # Start output buffer
        ob_start();
    }


    public function testCast2String(): void
    {
        # Run function
        echo new Role(self::$person);
        echo new Role(self::$people);

        # Assert result
        $this->assertEquals(ob_get_contents(), 'John DoeJohn Doe; Jane Done');
    }


    public function tearDown(): void
    {
        # Clear output buffer
        ob_end_clean();
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
