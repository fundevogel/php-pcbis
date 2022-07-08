<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Api;

use Fundevogel\Pcbis\Api\Webservice;

class WebserviceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testAvailableMethods(): void
    {
        # Setup
        $methods = [
            'login',
            'suche',
            'ola',
        ];

        # Run function
        $obj = new Pcbis();

        foreach ($methods as $method) {
            # Assert result
            $this->assertTrue(method_exists($obj, $method));
        }
    }


    public function testAvailableProperties(): void
    {
        # Setup
        $properties = [
            'headers',
            'url',
            'token',
        ];

        # Run function
        $obj = new Webservice();

        foreach ($properties as $property) {
            # Assert result
            $this->assertTrue(property_exists($obj, $property));
        }
    }


    public function testInit(): void
    {
        # Run function
        $obj = new Webservice();

        # Assert result
        $this->assertEquals($obj->headers, []);
        $this->assertIsString($obj->url);
        $this->assertNull($obj->token);
    }
}
