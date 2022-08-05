<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 4.0.0-rc.4
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Ola;
use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Classes\Product\Factory;
use Fundevogel\Pcbis\Classes\Product\Product;
use Fundevogel\Pcbis\Classes\Products\Collection;

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
     * Constructor
     *
     * @param array $credentials Login credentials
     * @return void
     */
    public function __construct(?array $credentials = null)
    {
        $this->api = new Webservice($credentials);
    }


    /**
     * Fetches data from API & formats it
     *
     * @param string $identifier Product EAN/ISBN
     * @return array Matched products (may be empty)
     */
    public function fetch(string $identifier): ?array
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
                    # Some EANs contain two numbers
                    # See '978-3-7891-2946-9'
                    if ($triple->feldName == 'EAN') {
                        # Determine EAN
                        $ean = $triple->werte[0];

                        # Skip mismatches
                        if ($ean != str_replace('-', '', $identifier)) {
                            break;
                        }

                        # Apply it
                        $data['EAN'] = $ean;

                        # Move on to next item
                        continue;
                    }

                    # If more than one value available ..
                    $data[$triple->feldName] = count($triple->werte) > 1
                        ? $triple->werte     # .. assign them all
                        : $triple->werte[0]  # .. otherwise first only
                    ;
                }

                # Skip mismatches
                if (!array_key_exists('EAN', $data)) {
                    continue;
                }

                return $data;
            }
        }

        return null;
    }


    /**
     * Instantiates 'Product' object from data OR single EAN/ISBN
     *
     * @param string $value Product EAN/ISBN OR its data
     * @return \Fundevogel\Pcbis\Classes\Product\Product
     */
    public function load(array|string $value): ?Product
    {
        # If value represents an identifier ..
        if (is_string($value)) {
            # .. fetch its product data
            $value = $this->fetch($value);
        }

        # If data is invalid ..
        if (is_null($value)) {
            # .. fail early
            return $value;
        }

        # Load product object
        # (1) Instantiate it
        $object = Factory::create($value);

        # (2) Pass API object
        $object->api = $this->api;

        return $object;
    }


    /**
     * Instantiates 'Products' object from multiple EANs/ISBNs
     *
     * @param array $identifiers Product EANs/ISBNs
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
     * @param \Fundevogel\Pcbis\Classes\Product\Product $object Product to be downgraded
     * @return \Fundevogel\Pcbis\Classes\Product\Product|self
     */
    public function downgrade(Product $object): Product|self
    {
        # If available ..
        if ($object->hasDowngrade()) {
            # .. attempt to ..
            if ($downgrade = $this->load($object->data['VorherigeAuflageGtin'])) {
                # .. perform downgrade
                return $downgrade;
            }
        }

        return $object;
    }


    /**
     * Performs upgrade (or returns product itself)
     *
     * @param \Fundevogel\Pcbis\Classes\Product\Product $object Product to be upgraded
     * @return \Fundevogel\Pcbis\Classes\Product\Product|self|null
     */
    public function upgrade(Product $object): Product|self|null
    {
        # If available ..
        if ($object->hasUpgrade()) {
            # .. attempt to ..
            if ($upgrade = $this->load($object->data['NeueAuflageGtin'])) {
                # .. perform upgrade
                return $upgrade;
            }
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
