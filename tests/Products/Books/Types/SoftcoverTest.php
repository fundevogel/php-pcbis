<?php

namespace Fundevogel\Pcbis\Tests\Products\Books\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Types\Softcover;

class SoftcoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Softcover(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isSoftcover());
    }
}
