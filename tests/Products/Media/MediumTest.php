<?php

namespace Fundevogel\Pcbis\Tests\Products\Media;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Media\Medium;

class MediumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Medium(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isMedia());
    }
}
