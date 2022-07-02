<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Books;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Book;

use org\bovigo\vfs\vfsStream;

class BookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Book(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isBook());
    }


    public function testDownloadCover(): void
    {
        # Setup
        # (1) Virtual directory
        $root = vfsStream::setup('home');

        # (2) Fixture file path
        $isbn = '978-3-314-10561-6';  # Die Tode meiner Mutter
        $fixture = sprintf('%s/fixtures/%s.jpg', __DIR__ . '/../..', $isbn);

        # (3) Output file path
        $path = $root->url() . '/example.jpg';

        # Run function
        $obj = new Book(['EAN' => $isbn], new Webservice());
        $result = $obj->downloadCover($path);

        # Assert result
        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertFileEquals($fixture, $path);
    }
}
