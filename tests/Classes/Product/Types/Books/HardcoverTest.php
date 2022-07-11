<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Types\Books;

use Fundevogel\Pcbis\Classes\Product\Types\Books\Hardcover;

class HardcoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Hardcover(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isHardcover());
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Hardcover(['EAN' => 'xxx']);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
