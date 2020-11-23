<?php

namespace PHPCBIS\Traits;

use PHPCBIS\Helpers\Butler;


/**
 * Trait Iteration
 *
 * Provides ability to act as iterable container holding `Product` objects
 *
 * @package PHPCBIS
 */

trait Iteration
{
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
}
