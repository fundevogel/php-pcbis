<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api\Exceptions\Types;

use Fundevogel\Pcbis\Api\Exceptions\Exception;

/**
 * Class OKException
 *
 * Indicates 'OK' exception (it's a KNV thing)
 */
class OKException extends Exception
{
}
