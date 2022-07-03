<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Nonbook;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Nonbook\Item;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Item(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isItem());
    }
}
