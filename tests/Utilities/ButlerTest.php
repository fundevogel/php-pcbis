<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis\Tests\Utilities;

use Fundevogel\Pcbis\Utilities\Butler;

use org\bovigo\vfs\vfsStream;

class ButlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testReverseName(): void
    {
        # Run function #1
        $result1 = Butler::reverseName('Doe, John');

        # Assert result #1
        $this->assertEquals($result1, 'John Doe');

        # Run function #2
        $result2 = Butler::reverseName('Doe# John', '#');

        # Assert result #2
        $this->assertEquals($result2, 'John Doe');
    }


    public function testDownloadCover(): void
    {
        # Setup
        # (1) Virtual directory
        $root = vfsStream::setup('home');

        # (2) Fixture file path
        $isbn = '978-3-314-10561-6';  # Die Tode meiner Mutter
        $fixture = sprintf('%s/../fixtures/%s.jpg', __DIR__, $isbn);

        # (3) Output file path
        $path = $root->url() . '/example.jpg';

        # Run function
        $result = Butler::downloadCover($isbn, $path);

        # Assert result
        if (class_exists('GuzzleHttp\Client')) {
            $this->assertTrue($result);
            $this->assertFileEquals($fixture, $path);
        } else {
            $this->assertFalse($result);
        }
    }
}
