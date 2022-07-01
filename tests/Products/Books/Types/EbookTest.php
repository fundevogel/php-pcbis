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
}
