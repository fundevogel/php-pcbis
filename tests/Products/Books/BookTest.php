<?php

namespace Fundevogel\Pcbis\Tests\Products\Books;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Book;

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
}
