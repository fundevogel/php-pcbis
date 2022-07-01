<?php

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Nonbook\Types\Calendar;

class CalendarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Calendar(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isCalendar());
    }
}
