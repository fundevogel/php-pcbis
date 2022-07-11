<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Fields\Types;

use Fundevogel\Pcbis\Classes\Product\Fields\Types\Series;

class SeriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * @var array
     */
    private static $data = [
        'Harry Potter' => 'Band 3: Der Gefangene von Askaban',
        'Best of Rowling' => '12 - HP 3',
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
        echo new Series(self::$data);

        # Assert result
        $this->assertEquals(ob_get_contents(), 'Harry Potter<br \>Best of Rowling');
    }


    public function tearDown(): void
    {
        # Clear output buffer
        ob_end_clean();
    }


    public function testToString(): void
    {
        # Run function #1
        $result = new Series(self::$data);

        # Assert result #1
        $this->assertEquals($result->toString('; '), 'Harry Potter; Best of Rowling');
    }


    public function testToArray(): void
    {
        # Run function #1
        $result = new Series(self::$data);

        # Assert result #1
        $this->assertEquals($result->toArray(), self::$data);
    }


    public function testSeries(): void
    {
        # Run function #1
        $result = new Series(self::$data);

        # Assert result #1
        $this->assertEquals($result->series(), ['Harry Potter', 'Best of Rowling']);
    }


    public function testVolumes(): void
    {
        # Run function #1
        $result = new Series(self::$data);

        # Assert result #1
        $this->assertEquals($result->volumes(), ['Band 3: Der Gefangene von Askaban', '12 - HP 3']);
    }
}
