<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Nonbook\Types;

use Fundevogel\Pcbis\Products\Nonbook\Types\Boardgame;

class BoardgameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Boardgame(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isItem());
        $this->assertTrue($obj->isBoardgame());
    }


    public function testPlayerCount(): void
    {
        # Run function
        $obj = new Boardgame(['EAN' => 'xxx', 'Utitel' => 'Für 1-4 Spieler. Spieldauer: 60-90 Min.']);

        # Assert result
        $this->assertEquals($obj->playerCount(), '1-4');
    }


    public function testPlayingTime(): void
    {
        # Run function
        $obj = new Boardgame(['EAN' => 'xxx', 'Utitel' => 'Für 1-4 Spieler. Spieldauer: 60-90 Min.']);

        # Assert result
        $this->assertEquals($obj->playingTime(), '60-90');
    }
}
