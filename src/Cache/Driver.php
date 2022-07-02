<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Cache;

abstract class Driver
{
    /**
     * Constructor
     *
     * @param mixed $cache Cache object
     * @return void
     */
    public function __construct(public mixed $cache)
    {
    }


    /**
     * Methods
     */

    /**
     * Retrieves item from cache
     *
     * @param string $key Cache identifier
     * @param mixed $default Default value
     * @return mixed
     */
    abstract public function get(string $key, mixed $default = null): mixed;


    /**
     * Removes item from cache
     *
     * @param string $key Cache identifier
     * @return bool Whether removal was successful
     */
    abstract public function delete(string $key): bool;
}
