<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Types\Media;

use Fundevogel\Pcbis\Classes\Product\Types\Media\Movie;

class MovieTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Movie(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isMedia());
        $this->assertTrue($obj->isMovie());
    }


    public function testAge(): void
    {
        # Run function
        $obj = new Movie(['EAN' => 'xxx', 'SonstTxt' => 'FSK ab 16 freigegeben']);

        # Assert result
        $this->assertEquals($obj->age(), 'ab 16 Jahren');
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Movie(['EAN' => 'xxx', 'SonstTxt' => 'FSK ab 16 freigegeben']);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
