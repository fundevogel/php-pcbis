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

    public function testCast2String(): void
    {
        # Run function #1
        $result1 = new Value(self::$string);

        # Assert result #1
        $this->assertEquals($result1->__toString(), 'string');

        # Run function #2
        $result2 = new Value(self::$array);

        # Assert result #2
        $this->assertEquals($result2->__toString(), 'value1<br \>value2');
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
