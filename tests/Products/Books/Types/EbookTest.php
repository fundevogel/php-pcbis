<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Books\Types;

use Fundevogel\Pcbis\Products\Books\Types\Ebook;

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
        $this->assertEquals($obj->devices(), ['PC', 'Mac', 'eReader', 'Tablet']);
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
}
