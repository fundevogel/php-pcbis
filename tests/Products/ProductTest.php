<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Product;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testEAN(): void
    {
        # Setup
        $ean = '978-3-314-10561-6';  # Die Tode meiner Mutter

        # Run function
        $obj = new Product(['EAN' => $ean], new Webservice());

        # Assert result
        $this->assertEquals($obj->ean(), $ean);
    }
}
