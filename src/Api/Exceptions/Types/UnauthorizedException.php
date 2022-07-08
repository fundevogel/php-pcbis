<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api\Exceptions;

use Fundevogel\Pcbis\Api\Exceptions\KNVException;

/**
 * Class UnauthorizedException
 *
 * Indicates 'Unauthorized' response
 */
class UnauthorizedException extends KNVException
{
}
