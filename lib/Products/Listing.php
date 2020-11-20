<?php

namespace PHPCBIS\Products;


/**
 * Class BookList
 *
 * Serves as template for collections of books
 *
 * @package PHPCBIS
 */

abstract class Listing implements \Countable, \Iterator
{
    /**
     * Group of `PHPCBIS\Book` objects
     *
     * @var array
     */
    private $books;


    /**
     * Constructor
     */

    public function __construct(array $books) {
        # Store books
        $this->books = $books;
    }


    /**
     * `Iterator` methods plus `prev`
     */

    /**
     * Returns the current book
     *
     * @return \PHPCBIS\Products\Books\Book
     */
    public function current()
    {
        return current($this->books);
    }


    /**
     * Returns the current key
     *
     * @return string
     */
    public function key()
    {
        return key($this->books);
    }


    /**
     * Moves the cursor to the next book and returns it
     *
     * @return \PHPCBIS\Products\Books\Book
     */
    public function next()
    {
        return next($this->books);
    }


    /**
     * Moves the cursor to the previous book and returns it
     *
     * @return \PHPCBIS\Products\Books\Book
     */
    public function prev()
    {
        return prev($this->books);
    }


    /**
     * Moves the cusor to the first book
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->books);
    }


    /**
     * Checks if the current book is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }


    /**
     * Methods
     */

    /**
     * Counts all books
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->books);
    }


    /**
     * Prepends a book to the data array
     *
     * @param mixed $key
     * @param mixed $item
     * @param mixed ...$args
     * @return \PHPCBIS\Products\Books\Books
     */
    public function prepend(...$args)
    {
        if (count($args) === 1) {
            array_unshift($this->books, $args[0]);
        } elseif (count($args) > 1) {
            $data = $this->books;
            $this->books = [];
            $this->set($args[0], $args[1]);
            $this->books += $data;
        }

        return $this;
    }


    /**
     * Appends a book
     *
     * @param mixed $key
     * @param mixed $item
     * @param mixed ...$args
     * @return \PHPCBIS\Products\Books\Books
     */
    public function append(...$args)
    {
        if (count($args) === 1) {
            $this->books[] = $args[0];
        } elseif (count($args) > 1) {
            $this->set($args[0], $args[1]);
        }

        return $this;
    }


    /**
     * Returns the books in reverse order
     *
     * @return \PHPCBIS\Products\Books\Books
     */
    public function flip()
    {
        $collection = clone $this;
        $collection->books = array_reverse($this->books, true);

        return $collection;
    }


    /**
     * Returns the first book
     *
     * @return \PHPCBIS\Products\Books\Book
     */
    public function first()
    {
        return array_shift($this->books);
    }


    /**
     * Returns the last book
     *
     * @return \PHPCBIS\Products\Books\Book
     */
    public function last()
    {
        return array_pop($this->books);
    }


    /**
     * Returns the nth book from the collection
     *
     * @param int $n
     * @return \PHPCBIS\Products\Books\Book
     */
    public function nth(int $n)
    {
        return array_values($this->books)[$n] ?? null;
    }


    /**
     * Returns a Collection without the given book(s)
     *
     * @param string ...$keys any number of keys, passed as individual arguments
     * @return \PHPCBIS\Products\Books\Books
     */
    public function not(...$keys)
    {
        $collection = clone $this;

        foreach ($keys as $key) {
            unset($collection->books[$key]);
        }

        return $collection;
    }


    /**
     * Checks if the number of books is zero
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }


    /**
     * Checks if the number of books is more than zero
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }


    /**
     * Checks if the number of books is even
     *
     * @return bool
     */
    public function isEven(): bool
    {
        return $this->count() % 2 === 0;
    }


    /**
     * Checks if the number of books is odd
     *
     * @return bool
     */
    public function isOdd(): bool
    {
        return $this->count() % 2 !== 0;
    }


    /**
     * Map a function to each book
     *
     * @param callable $callback
     * @return \PHPCBIS\Products\Books\Books
     */
    public function map(callable $callback)
    {
        $this->books = array_map($callback, $this->books);

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

        foreach ($this->books as $item) {
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
