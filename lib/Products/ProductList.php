<?php

namespace Pcbis\Products;

use Pcbis\Products\Products;


/**
 * Class ProductList
 *
 * Represents group of `Product` objects
 *
 * @package PHPCBIS
 */

class ProductList extends Products
{
    /**
     * Methods
     */

    /**
     * Gets `Product` from EAN/ISBN
     *
     * @param string $identifier - Product EAN/ISBN
     * @return \Pcbis\Products\Product|false
     */
    public function getISBN(string $identifier)
    {
        foreach ($this->data as $item) {
            if ($identifier === $item->isbn()) {
                return $item;
            }
        }

        return false;
    }
}
