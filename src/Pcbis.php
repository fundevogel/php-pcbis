<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 3.0.0
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Products\Factory;
use Fundevogel\Pcbis\Products\Product;

/**
 * Class Pcbis
 *
 * Base class for everything pcbis.de
 */
final class Pcbis
{
    /**
     * Properties
     */

    /**
     * Webservice API client
     *
     * @var \Fundevogel\Pcbis\Api\Webservice
     */
    public Webservice $api;


    /**
     * Whether to update cached data
     *
     * @var bool
     */
    public bool $forceRefresh = false;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @param string $cache Cache object
     * @return void
     */
    public function __construct(?array $credentials = null, public mixed $cache = null)
    {
        $this->api = new Webservice($credentials);
    }


    /**
     * Formats query results (helper function)
     *
     * @param string $identifier Product EAN/ISBN
     * @return array Matched products
     */
    private function _fetch(string $identifier): array
    {
        # Query API for matching search items
        $result = $this->api->suche($identifier);

        if ($result->suchenAntwort->gesamtTreffer > 0) {
            # Create data array
            $data = [];

            foreach (A::first($result->lesenAntwort->titel)->einzelWerk as $item) {
                if (count($item->werte) > 1) {
                    $data[$item->feldName] = $item->werte;
                } else {
                    $data[$item->feldName] = $item->werte[0];
                }
            }

            return $data;
        }

        return [];
    }


    /**
     * Fetches information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     * @return array
     */
    public function fetch(string $identifier, bool $forceRefresh = false): array
    {
        $value = null;

        if (!is_null($this->cache)) {
            # If specified ..
            if ($forceRefresh) {
                # .. clear cache beforehand
                $this->cache->delete($identifier);
            }

            # Retrieve from cache
            $value = $this->cache->get($identifier, function () use ($identifier) {
                return $this->_fetch($identifier);
            });
        }

        if (is_null($value)) {
            $value = $this->_fetch($identifier);
        }

        return $value;
    }


    /**
     * Instantiates 'Product' object from single EAN/ISBN
     *
     * @param string $identifier Product EAN/ISBN
     * @return \Fundevogel\Pcbis\Products\Product|null
     */
    public function load(string $identifier): Product|null
    {
        # If raw data is available ..
        if ($data = $this->fetch($identifier, $this->forceRefresh)) {
            # .. instantiate
            return Factory::create($data, $this->api);
        }

        return null;
    }


    /**
     * Performs downgrade (or returns product itself)
     *
     * @param \Fundevogel\Pcbis\Products\Product $object Product to be downgraded
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public function downgrade(Product $object): Product
    {
        # If available ..
        if ($object->hasDowngrade()) {
            # .. perform downgrade
            return $this->load($object->data['VorherigeAuflageGtin']);
        }

        return $object;
    }


    /**
     * Performs upgrade (or returns product itself)
     *
     * @param \Fundevogel\Pcbis\Products\Product $object Product to be upgraded
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public function upgrade(Product $object): Product
    {
        # If available ..
        if ($object->hasUpgrade()) {
            # .. perform upgrade
            return $this->load($object->data['NeueAuflageGtin']);
        }

        return $object;
    }


    /**
     * Checks product availability via OLA ('Online Lieferbarkeits-Abfrage')
     *
     * @param string $identifier Product EAN/ISBN
     * @param int $quantity Number of products to be delivered
     * @param string $type OLA type (either 'anfrage', 'bestellung' or 'storno')
     * @return \Fundevogel\Pcbis\Api\Ola
     */
    public function ola(string $identifier, int $quantity = 1, string $type = 'anfrage'): Ola
    {
        return new Ola($this->api->ola($identifier, $quantity, $type));
    }
}
