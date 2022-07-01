<?php

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Nonbook\Types\Map;

class MapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Map(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isMap());
    }
}
