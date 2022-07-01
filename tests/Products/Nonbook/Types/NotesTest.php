<?php

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Nonbook\Types\Notes;

class NotesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Notes(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isNotes());
    }
}
