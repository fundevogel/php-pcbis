<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Media\Types;

use Fundevogel\Pcbis\Classes\Product\Media\Types\Sound;

class SoundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Sound(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isMedia());
        $this->assertTrue($obj->isSound());
    }
}
