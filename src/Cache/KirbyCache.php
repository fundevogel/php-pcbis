<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Cache;

class KirbyCache extends Driver
{
    /**
     * Methods
     */

    /**
     * Retrieves item from cache
     *
     * @param string $key Cache identifier
     * @param callback $callback Callback value
     * @return mixed
     */
    public function get(string $key, callback $callback): mixed
    {
        if (!$this->cache->exists($key)) {
            $this->cache->set($key, $callback());
        }

        return $this->cache->get($key);
    }


    /**
     * Removes item from cache
     *
     * @param string $key Cache identifier
     * @return bool Whether removal was successful
     */
    public function delete(string $key): bool
    {
        return $this->cache->remove($key);
    }
}
