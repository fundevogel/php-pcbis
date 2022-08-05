<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests;

use Fundevogel\Pcbis\Pcbis;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Interfaces\Product;

class PcbisTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testAvailableMethods(): void
    {
        # Setup
        $methods = [
            'load',
            'downgrade',
            'upgrade',
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
        # Run function
        $obj = new Pcbis();

        # Assert result
        $this->assertTrue(property_exists($obj, 'api'));
    }


    public function testInit(): void
    {
        # Run function
        $obj = new Pcbis();

        # Assert result
        $this->assertInstanceOf(Webservice::class, $obj->api);
    }


    public function testLoad(): void
    {
        # Setup
        $data = [
            'EAN' => 'xxx',
            'Sortimentskennzeichen' => 'HC',
            'AutorSachtitel' => 'Doe, John',
            'Titel' => 'Title',
            'Utitel' => 'Subtitle',
            'IndexVerlag' => 'Verlag',
        ];

        # Run function
        $obj = (new Pcbis())->load($data);

        # Assert result
        $this->assertInstanceOf(Product::class, $obj);
    }
}
