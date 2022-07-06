<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Products\Nonbook\Types\Software;

class SoftwareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Software(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isSoftware());
    }


    public function testVersion(): void
    {
        # Run function #1
        $obj = new Software(['EAN' => 'xxx', 'Titel' => 'Fritz & Fertig, Sonderedition 2 in 1, 2 CD-ROMs', 'Abb' => '2015. 192 x 139 mm']);

        # Assert result #1
        $this->assertEquals($obj->version(), '');

        # Run function #2
        $obj = new Software(['EAN' => 'xxx', 'Abb' => 'Version 28.00 2020. 19,5 cm']);

        # Assert result #2
        $this->assertEquals($obj->version(), '28.00');
    }
}
