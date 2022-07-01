<?php

namespace Fundevogel\Pcbis\Tests\Products\Books\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Types\Schoolbook;

class SchoolbookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Schoolbook(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isSchoolbook());
    }
}
