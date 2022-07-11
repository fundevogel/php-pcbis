<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Fields;

use Fundevogel\Pcbis\Classes\Product\Fields\Value;

class ValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * @var array
     */
    private static $string = 'string';


    /**
     * @var array
     */
    private static $array = [
        'key1' => 'value1',
        'key2' => 'value2',
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
        # Run function #1
        echo new Value(self::$string);
        echo new Value(self::$array);

        # Assert result #1
        $this->assertEquals(ob_get_contents(), 'stringvalue1<br \>value2');
    }


    public function tearDown(): void
    {
        # Clear output buffer
        ob_end_clean();
    }


    public function testToString(): void
    {
        # Run function #1
        $result = new Value(self::$string);

        # Assert result #1
        $this->assertEquals($result->toString(), 'string');

        # Run function #2
        $result = new Value(self::$array);

        # Assert result #2
        $this->assertEquals($result->toString('; '), 'value1; value2');
    }


    public function testToArray(): void
    {
        # Run function #1
        $result = new Value(self::$string);

        # Assert result #1
        $this->assertEquals($result->toArray(), ['string']);

        # Run function #2
        $result = new Value(self::$array);

        # Assert result #2
        $this->assertEquals($result->toArray(), self::$array);
    }


    public function testValue(): void
    {
        # Run function #1
        $result = new Value(self::$string);

        # Assert result #1
        $this->assertEquals($result->value(), self::$string);

        # Run function #2
        $result = new Value(self::$array);

        # Assert result #2
        $this->assertEquals($result->value(), self::$array);
    }
}
