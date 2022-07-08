<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product;

use Fundevogel\Pcbis\Classes\Product\ProductBase;

class ProductBaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testAvailableMethods(): void
    {
        # Setup
        $methods = [
            'ean',
            'type',
            'isBook',
            'isEbook',
            'isHardcover',
            'isSchoolbook',
            'isSoftcover',
            'isMedia',
            'isAudiobook',
            'isMovie',
            'isMusic',
            'isSound',
            'isItem',
            'isBoardgame',
            'isCalendar',
            'isMap',
            'isNonbook',
            'isNotes',
            'isSoftware',
            'isStationery',
            'isToy',
            'isVideogame',
        ];

        # Run function
        $obj = $this->getMockForAbstractClass(ProductBase::class, [['EAN' => 'xxx']]);

        foreach ($methods as $method) {
            # Assert result
            $this->assertTrue(method_exists($obj, $method));
        }
    }
}
