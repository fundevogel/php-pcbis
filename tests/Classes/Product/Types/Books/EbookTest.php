<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Types\Books;

use Fundevogel\Pcbis\Classes\Product\Types\Books\Ebook;

class EbookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isEbook());
    }


    public function testSubtitle(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'Utitel' => 'Unterstützte Lesegerätegruppen: PC/MAC/eReader/Tablet']);

        # Assert result
        $this->assertEquals($obj->subtitle(), '');
    }


    public function testDevices(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'Utitel' => 'Unterstützte Lesegerätegruppen: PC/MAC/eReader/Tablet']);

        # Assert result
        $this->assertEquals($obj->devices()->value(), ['PC', 'Mac', 'eReader', 'Tablet']);
    }


    public function testPrintEdition(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'PrintISBN' => '9783522202107']);

        # Assert result
        $this->assertEquals($obj->printEdition(), '9783522202107');
    }


    public function testFileSize(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'DateiGroesse' => '10229 KB']);

        # Assert result
        $this->assertEquals($obj->fileSize(), '9.99 MB');
    }


    public function testFileFormat(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'DateiFormat' => 'EPUB']);

        # Assert result
        $this->assertEquals($obj->fileFormat(), 'epub');
    }


    public function testDRM(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx', 'DRMFlags' => '02']);

        # Assert result
        $this->assertEquals($obj->drm(), 'Digitales Wasserzeichen');
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Ebook([
            'EAN' => 'xxx',
            'Utitel' => 'Unterstützte Lesegerätegruppen: PC/MAC/eReader/Tablet',
            'PrintISBN' => '9783522202107',
            'DateiGroesse' => '10229 KB',
            'DateiFormat' => 'EPUB',
            'DRMFlags' => '02',
        ]);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
