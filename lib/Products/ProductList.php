<?php

namespace PHPCBIS\Products;

use Countable;
use Iterator;


/**
 * Class ProductList
 *
 * Serves as template for collections of `Product` objects
 *
 * @package PHPCBIS
 */

abstract class ProductList implements Countable, Iterator
{
    /**
     * Properties
     */

    /**
     * Group of `PHPCBIS\Products\Product` objects
     *
     * @var array
     */
    private $data;


    /**
     * Constructor
     */

    public function __construct(array $data) {
        # Store objects
        $this->data = $data;
    }


    /**
     * Methods
     */

    /**
     * 1) Countable
     */

    /**
     * Counts all objects
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }


    /**
     * 2) Iterable
     */

    /**
     * Methods
     */

    /**
     * Returns the current object
     *
     * @return \PHPCBIS\Products\Product
     */
    public function current()
    {
        return current($this->data);
    }


    /**
     * Returns the current key
     *
     * @return string
     */
    public function key()
    {
        return key($this->data);
    }


    /**
     * Moves the cursor to the next object and returns it
     *
     * @return \PHPCBIS\Products\Product
     */
    public function next()
    {
        return next($this->data);
    }


    /**
     * Moves the cursor to the previous object and returns it
     *
     * @return \PHPCBIS\Products\Product
     */
    public function prev()
    {
        return prev($this->data);
    }


    /**
     * Moves the cusor to the first object
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }


    /**
     * Checks if the current object is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }


    /**
     * 3) Utilities
     */

    /**
     * Prepends an object to the data array
     *
     * @param mixed $key
     * @param mixed $item
     * @param mixed ...$args
     * @return \PHPCBIS\Products\ProductList
     */
    public function prepend(...$args)
    {
        if (count($args) === 1) {
            array_unshift($this->data, $args[0]);
        } elseif (count($args) > 1) {
            $data = $this->data;
            $this->data = [];
            $this->set($args[0], $args[1]);
            $this->data += $data;
        }

        return $this;
    }


    /**
     * Appends an object
     *
     * @param mixed $key
     * @param mixed $item
     * @param mixed ...$args
     * @return \PHPCBIS\Products\ProductList
     */
    public function append(...$args)
    {
        if (count($args) === 1) {
            $this->data[] = $args[0];
        } elseif (count($args) > 1) {
            $this->set($args[0], $args[1]);
        }

        return $this;
    }


    /**
     * Returns the objects in reverse order
     *
     * @return \PHPCBIS\Products\ProductList
     */
    public function flip()
    {
        $collection = clone $this;
        $collection->data = array_reverse($this->data, true);

        return $collection;
    }


    /**
     * Returns the first object
     *
     * @return \PHPCBIS\Products\Product
     */
    public function first()
    {
        return array_shift($this->data);
    }


    /**
     * Returns the last object
     *
     * @return \PHPCBIS\Products\Product
     */
    public function last()
    {
        return array_pop($this->data);
    }


    /**
     * Returns the nth object from the collection
     *
     * @param int $n
     * @return \PHPCBIS\Products\Product
     */
    public function nth(int $n)
    {
        return array_values($this->data)[$n] ?? null;
    }


    /**
     * Returns a Collection without the given object(s)
     *
     * @param string ...$keys any number of keys, passed as individual arguments
     * @return \PHPCBIS\Products\ProductList
     */
    public function not(...$keys)
    {
        $collection = clone $this;

        foreach ($keys as $key) {
            unset($collection->data[$key]);
        }

        return $collection;
    }


    /**
     * Checks if the number of objects is zero
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }


    /**
     * Checks if the number of objects is more than zero
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }


    /**
     * Checks if the number of objects is even
     *
     * @return bool
     */
    public function isEven(): bool
    {
        return $this->count() % 2 === 0;
    }


    /**
     * Checks if the number of objects is odd
     *
     * @return bool
     */
    public function isOdd(): bool
    {
        return $this->count() % 2 !== 0;
    }


    /**
     * Map a function to each object
     *
     * @param callable $callback
     * @return \PHPCBIS\Products\ProductList
     */
    public function map(callable $callback)
    {
        $this->data = array_map($callback, $this->data);

        return $this;
    }


    /**
     * Extracts all values for a single field into a new array
     *
     * @param string $field
     * @param string|null $split
     * @param bool $unique
     * @return array
     */
    public function pluck(string $field, string $split = null, bool $unique = false): array
    {
        $result = [];

        foreach ($this->data as $item) {
            $row = $this->getAttribute($item, $field);

            if ($split !== null) {
                $result = array_merge($result, Str::split($row, $split));
            } else {
                $result[] = $row;
            }
        }

        if ($unique === true) {
            $result = array_unique($result);
        }

        return array_values($result);
    }
}
