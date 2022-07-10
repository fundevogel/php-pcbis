<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Products;

use Fundevogel\Pcbis\Helpers\Iterator;
use Fundevogel\Pcbis\Interfaces\Product;
use Fundevogel\Pcbis\Interfaces\Products;

use Countable;

/**
 * Class Collection
 *
 * Base class for product collections
 */
class Collection extends Iterator implements Countable, Products
{
    /**
     * Constructor
     *
     * @array $data Products
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->set($data);
    }


    /**
     * Magic methods
     */

    /**
     * Setter
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function __set(string $key, mixed $value)
    {
        # Set its value
        $this->data[$this->strip($key)] = $value;

        return $this;
    }


    /**
     * Getter
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get(mixed $key): mixed
    {
        # If key (most likely) resembles EAN/ISBN ..
        if (is_string($key)) {
            # .. remove hyphens first
            $key = $this->strip($key);
        }

        # Get its value
        return $this->data[$key] ?? null;
    }


    /**
     * Remover
     *
     * @param mixed $key
     * @return void
     */
    public function __unset(mixed $key): void
    {
        # If key (most likely) resembles EAN/ISBN ..
        if (is_string($key)) {
            # .. remove hyphens first
            $key = $this->strip($key);
        }

        unset($this->data[$key]);
    }


    /**
     * Exports keys when casting to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }


    /**
     * Methods
     */

    /**
     * Adds product(s)
     *
     * @param \Fundevogel\Pcbis\Classes\Product\Product|\Fundevogel\Pcbis\Classes\Products\Collection $object
     * @return self
     */
    public function add(Product|Collection $object): self
    {
        if (is_a($object, self::class)) {
            foreach ($object->data as $item) {
                $this->append($item);
            }
        } else {
            $this->append($object);
        }

        return $this;
    }


    /**
     * Appends single product
     *
     * @param \Fundevogel\Pcbis\Interfaces\Product $object Product
     * @return self
     */
    public function append(Product $object): self
    {
        $this->set($object->ean(), $object);

        return $this;
    }


    /**
     * Clones instance
     *
     * @return self
     */
    public function clone(): self
    {
        return clone $this;
    }


    /**
     * Provides empty instance clone
     *
     * @return static
     */
    public function empty(): static
    {
        $clone = clone $this;
        $clone->data = [];

        return $clone;
    }


    /**
     * Finds single product by product EAN/ISBN
     *
     * @param string $key
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public function findByKey(string $identifier): ?Product
    {
        return $this->get($identifier);
    }


    /**
     * Exports first product
     *
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public function first(): ?Product
    {
        $array = $this->data;

        return array_shift($array);
    }


    /**
     * Reverses product order
     *
     * @return static
     */
    public function flip(): static
    {
        $collection = clone $this;
        $collection->data = array_reverse($this->data, true);

        return $collection;
    }


    /**
     * Getter
     *
     * @param mixed $key
     * @param mixed $default
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public function get(mixed $key, ?Product $default = null): ?Product
    {
        return $this->__get($key) ?? $default;
    }


    /**
     * Exports last product
     *
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public function last(): ?Product
    {
        $array = $this->data;

        return array_pop($array);
    }


    /**
     * Prepends single product
     *
     * @param \Fundevogel\Pcbis\Interfaces\Product $object Product
     * @return self
     */
    public function prepend(Product $object): self
    {
        # Store data
        $data = $this->data;

        # Clear data
        $this->data = [];

        # Add product
        $this->set($object->ean(), $object);

        # Readd data
        $this->data += $data;

        return $this;
    }


    /**
     * Remover
     *
     * @param mixed $key
     * @return self
     */
    public function remove(mixed $key): self
    {
        $this->__unset($key);

        return $this;
    }


    /**
     * Setter
     *
     * @param array|string $key
     * @param \Fundevogel\Pcbis\Interfaces\Product $value Product object
     * @return self
     */
    public function set(array|string $key, ?Product $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $item) {
                $this->__set($item->ean(), $item);
            }
        } else {
            $this->__set($key, $value);
        }

        return $this;
    }


    /**
     * Converts products to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * Converts products to JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }


    /**
     * Converts products to string
     *
     * @return string
     */
    public function toString(): string
    {
        return implode('<br />', $this->keys());
    }


    /**
     * Helpers
     */

    private function strip(string $string): string
    {
        return str_replace('-', '', $string);
    }
}
