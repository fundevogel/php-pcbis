<?php

namespace Fundevogel\Pcbis\Tests\Products\Media\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Media\Types\Audiobook;

class AudiobookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Audiobook(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isMedia());
        $this->assertTrue($obj->isAudiobook());
    }
}
