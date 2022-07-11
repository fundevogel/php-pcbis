<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Types\Media;

use Fundevogel\Pcbis\Classes\Product\Types\Media\Audiobook;

class AudiobookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Audiobook(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isMedia());
        $this->assertTrue($obj->isAudiobook());
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Audiobook(['EAN' => 'xxx']);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
