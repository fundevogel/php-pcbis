<?php

namespace Fundevogel\Pcbis\Tests\Products\Books\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Types\Ebook;

class EbookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Ebook(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isEbook());
    }


    public function testSubtitle(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'Utitel' => 'Unterstützte Lesegerätegruppen: PC/MAC/eReader/Tablet',
        ];


        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->subtitle(), '');
    }


    public function testDevices(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'Utitel' => 'Unterstützte Lesegerätegruppen: PC/MAC/eReader/Tablet',
        ];

        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->devices(), [
            'PC',
            'Mac',
            'eReader',
            'Tablet',
        ]);
    }


    public function testPrintEdition(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'PrintISBN' => '9783522202107',
        ];

        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->printEdition(), '9783522202107');
    }


    public function testFileSize(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'DateiGroesse' => '10229 KB',
        ];

        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->fileSize(), '9.99 MB');
    }


    public function testFileFormat(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'DateiFormat' => 'EPUB',
        ];

        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->fileFormat(), 'epub');
    }


    public function testDRM(): void
    {

        # Setup
        $data = [
            'EAN' => '978-3-522-62111-3',
            'DRMFlags' => '02',
        ];

        # Run function
        $obj = new Ebook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->drm(), 'Digitales Wasserzeichen');
    }
}
