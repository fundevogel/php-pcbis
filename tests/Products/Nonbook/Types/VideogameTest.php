<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Nonbook\Types\Videogame;

class VideogameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Videogame(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isVideogame());
    }


    public function testAge(): void
    {
        # Run function
        $obj = new Videogame(['EAN' => 'xxx', 'SonstTxt' => 'USK ab 12 freigegeben. 2520040'], new Webservice());

        # Assert result
        $this->assertEquals($obj->age(), 'ab 12 Jahren');
    }


    public function testPlatforms(): void
    {
        # Run function
        $obj = new Videogame(['EAN' => 'xxx', 'AutorSachtitel' => 'The Legend of Zelda, Breath of the Wild, 1 Nintendo Switch-Spiel'], new Webservice());

        # Assert result
        $this->assertEquals($obj->platforms(), ['Nintendo Switch']);
    }
}
