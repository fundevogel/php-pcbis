<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Products;

use Fundevogel\Pcbis\Classes\Product\Types\Book;
use Fundevogel\Pcbis\Classes\Product\Types\Medium;
use Fundevogel\Pcbis\Classes\Product\Types\Item;
use Fundevogel\Pcbis\Classes\Products\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testCount(): void
    {
        # Setup
        $data = [
            new Book(['EAN' => 'a']),
            new Medium(['EAN' => 'b']),
            new Item(['EAN' => 'c']),
        ];

        # Run function
        $obj = new Collection($data);

        # Assert result
        $this->assertEquals(count($obj), 3);
        $this->assertEquals($obj->count(), 3);
    }
}
