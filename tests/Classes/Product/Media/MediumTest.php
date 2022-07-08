<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Media;

use Fundevogel\Pcbis\Classes\Product\Media\Medium;

class MediumTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Medium(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isMedia());
    }


    public function testDuration(): void
    {
        # Run function
        $obj = new Medium(['EAN' => 'xxx', 'Utitel' => '104 Min.']);

        # Assert result
        $this->assertEquals($obj->duration(), '104');
    }
}
