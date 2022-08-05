<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Utilities;

use Fundevogel\Pcbis\Utilities\Butler;

class ButlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testReverseName(): void
    {
        # Run function #1
        $result1 = Butler::reverseName('Doe, John');

        # Assert result #1
        $this->assertEquals($result1, 'John Doe');

        # Run function #2
        $result2 = Butler::reverseName('Doe# John', '#');

        # Assert result #2
        $this->assertEquals($result2, 'John Doe');
    }
}
