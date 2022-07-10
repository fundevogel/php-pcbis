<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 4.0.0-rc.2
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Classes\Product\Factory;
use Fundevogel\Pcbis\Classes\Products\Collection;
use Fundevogel\Pcbis\Interfaces\Product;

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
            # .. iterate over its hits
            foreach ($result->lesenAntwort->titel as $item) {
                # Create data array
                $data = [];

                # Iterate over data triples
                foreach ($item->einzelWerk as $triple) {
                    # If more than one value available ..
                    $data[$triple->feldName] = count($triple->werte) > 1
                        ? $triple->werte     # .. assign them all
                        : $triple->werte[0]  # .. otherwise first only
                    ;
                }

                # Skip mismatches
                if ($data['EAN'] != str_replace('-', '', $identifier)) {
                    continue;
                }

                return $data;
            }
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
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public function load(string $identifier): ?Product
    {
        # If raw data is available ..
        if ($data = $this->fetch($identifier, $this->forceRefresh)) {
            # .. instantiate product
            $object = Factory::create($data);

            # .. pass API object
            $object->api = $this->api;

            return $object;
        }

        return null;
    }


    /**
     * Instantiates 'Products' object from multiple EANs/ISBNs
     *
     * @param string $identifier Product EANs/ISBNs
     * @return \Fundevogel\Pcbis\Classes\Products\Collection
     */
    public function loadAll(array $identifiers): ?Collection
    {
        $collection = new Collection();

        foreach ($identifiers as $identifier) {
            if ($object = $this->load($identifier)) {
                $collection->add($object);
            }
        }

        return $collection;
    }


    /**
     * Performs downgrade (or returns product itself)
     *
     * @param \Fundevogel\Pcbis\Interfaces\Product $object Product to be downgraded
     * @return \Fundevogel\Pcbis\Interfaces\Product|self
     */
    public function downgrade(Product $object): Product|self
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
     * @param \Fundevogel\Pcbis\Interfaces\Product $object Product to be upgraded
     * @return \Fundevogel\Pcbis\Interfaces\Product|self
     */
    public function upgrade(Product $object): Product|self
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
