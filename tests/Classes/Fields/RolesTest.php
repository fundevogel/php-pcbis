<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis\Tests\Classes\Fields;

use Fundevogel\Pcbis\Classes\Fields\Roles;

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

    public function setUp(): void
    {
        # Start output buffer
        ob_start();
    }


    public function testCast2String(): void
    {
        # Run function
        echo new Roles(self::$people);

        # Assert result
        $this->assertEquals(ob_get_contents(), 'Illustration: John Doe. Mitarbeit: John Doe; Jane Done');
    }


    public function tearDown(): void
    {
        # Clear output buffer
        ob_end_clean();
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
        $this->assertEquals($result->toArray(), self::$people);
    }
}
