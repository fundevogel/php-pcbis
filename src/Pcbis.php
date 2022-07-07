<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 4.0.0-alpha
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
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
     * Retrieves data from API & formats it
     *
     * @param string $identifier Product EAN/ISBN
     * @return array Matched products (may be empty)
     */
    private function retrieve(string $identifier): array
    {
        # Query API for matching search items
        $result = $this->api->suche($identifier);

        # If search was successful ..
        if ($result->suchenAntwort->gesamtTreffer > 0) {
            # .. formatting data of each hit
            return array_map(function (array $array): array {
                # Create data array
                $data = [];

                # Iterate over each item
                foreach ($array->einzelWerk as $item) {
                    # If more than one value available ..
                    $data[$item->feldName] = count($item->werte) > 1
                        ? $item->werte     # .. assign them all
                        : $item->werte[0]  # .. otherwise first only
                    ;
                }

                return $data;
            # .. while iterating over results
            }, $result->lesenAntwort->titel);
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
    private function fetch(string $identifier, bool $forceRefresh = false): array
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
                return $this->retrieve($identifier);
            });
        }

        if (is_null($value)) {
            $value = $this->retrieve($identifier);
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
            return Factory::create($data[0], $this->api);
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
