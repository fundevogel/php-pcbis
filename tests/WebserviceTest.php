<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Exceptions\InvalidLoginException;

class WebserviceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testLoginInvalid(): void
    {
        # Setup
        # (1) Invalid login credentials
        $array = [
            'VKN'      => '123',
            'Benutzer' => '123',
            'Passwort' => '123',
        ];

        # Assert exception
        $this->expectException(InvalidLoginException::class);

        # Run function
        $result = new Webservice($array);
    }
}
