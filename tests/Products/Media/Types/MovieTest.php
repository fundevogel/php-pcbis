<?php

namespace Fundevogel\Pcbis\Tests\Products\Media\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Media\Types\Movie;

class MovieTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Movie(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isMedia());
        $this->assertTrue($obj->isMovie());
    }
}
