<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product;

use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Interfaces\Product;

/**
 * Class ProductBase
 *
 * Template for all product types
 */
abstract class ProductBase implements Product
{
    /**
     * Properties
     */

    /**
     * Product EAN/ISBN
     *
     * @var string
     */
    protected $identifier;


    /**
     * Constructor
     *
     * @param array $data Source data as fetched from KNV's API
     * @param \Fundevogel\Pcbis\Api\Webservice $api Object granting access to KNV's API
     * @return void
     */
    public function __construct(public array $data, protected ?Webservice $api = null)
    {
        # If not specified ..
        if (is_null($this->api)) {
            # .. invoke API client in offline mode
            $this->api = new Webservice();
        }

        # Add startup hook
        $this->setup();
    }


    /**
     * Setup hook
     *
     * @return void
     */
    abstract public function setup(): void;


    /**
     * Dataset methods
     */

    /**
     * Exports European Article Number (EAN)
     *
     * @return string
     */
    public function ean(): string
    {
        return $this->identifier;
    }


    /**
     * Exports title
     *
     * @return string
     */
    abstract public function title(): string;


    /**
     * Exports subtitle
     *
     * @return string
     */
    abstract public function subtitle(): string;


    /**
     * Exports retail price (in €)
     *
     * @return string
     */
    abstract public function retailPrice(): string;


    /**
     * Exports release year
     *
     * @return string
     */
    abstract public function releaseYear(): string;


    /**
     * Exports type of value added tax (VAT)
     *
     * @return string
     */
    abstract public function vat(): string;


    /**
     * Exports all data
     *
     * @return array
     */
    abstract public function export(): array;


    /**
     * Helpers
     */

    /**
     * Detects product type
     *
     * @return string
     */
    public function type(): string
    {
        # Extract class name (= product type)
        return A::last(explode('\\', get_class($this)));
    }


    /**
     * Checks whether this is a book
     *
     * @return bool
     */
    public function isBook(): bool
    {
        return in_array($this->type(), [
            # Base
            'Book',

            # Subset
            'Ebook',
            'Hardcover',
            'Schoolbook',
            'Softcover',
        ]);
    }


    /**
     * Checks whether this is an eBook
     *
     * @return bool
     */
    public function isEbook(): bool
    {
        return $this->type() == 'Ebook';
    }


    /**
     * Checks whether this is a hardcover book
     *
     * @return bool
     */
    public function isHardcover(): bool
    {
        return $this->type() == 'Hardcover';
    }


    /**
     * Checks whether this is a schoolbook
     *
     * @return bool
     */
    public function isSchoolbook(): bool
    {
        return $this->type() == 'Schoolbook';
    }


    /**
     * Checks whether this is a softcover book
     *
     * @return bool
     */
    public function isSoftcover(): bool
    {
        return $this->type() == 'Softcover';
    }


    /**
     * Checks whether this is a medium
     *
     * @return bool
     */
    public function isMedia(): bool
    {
        return in_array($this->type(), [
            # Base
            'Medium',

            # Subset
            'Audiobook',
            'Movie',
            'Music',
            'Sound',
        ]);
    }


    /**
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->type() == 'Audiobook';
    }


    /**
     * Checks whether this is a movie
     *
     * @return bool
     */
    public function isMovie(): bool
    {
        return $this->type() == 'Movie';
    }


    /**
     * Checks whether this is music
     *
     * @return bool
     */
    public function isMusic(): bool
    {
        return $this->type() == 'Music';
    }


    /**
     * Checks whether this is a sound storage medium
     *
     * @return bool
     */
    public function isSound(): bool
    {
        return $this->type() == 'Sound';
    }


    /**
     * Checks whether this is an item
     *
     * @return bool
     */
    public function isItem(): bool
    {
        return in_array($this->type(), [
            # Base
            'Item',

            # Subset
            'Boardgame',
            'Calendar',
            'Map',
            'Nonbook',
            'Notes',
            'Software',
            'Stationery',
            'Toy',
            'Videogame',
        ]);
    }


    /**
     * Checks whether this is a boardgame
     *
     * @return bool
     */
    public function isBoardgame(): bool
    {
        return $this->type() == 'Boardgame';
    }


    /**
     * Checks whether this is a calendar
     *
     * @return bool
     */
    public function isCalendar(): bool
    {
        return $this->type() == 'Calendar';
    }


    /**
     * Checks whether this is a map
     *
     * @return bool
     */
    public function isMap(): bool
    {
        return $this->type() == 'Map';
    }


    /**
     * Checks whether this is a generic item
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return $this->type() == 'Nonbook';
    }


    /**
     * Checks whether these are notes
     *
     * @return bool
     */
    public function isNotes(): bool
    {
        return $this->type() == 'Notes';
    }


    /**
     * Checks whether this is software
     *
     * @return bool
     */
    public function isSoftware(): bool
    {
        return $this->type() == 'Software';
    }


    /**
     * Checks whether this is stationery
     *
     * @return bool
     */
    public function isStationery(): bool
    {
        return $this->type() == 'Stationery';
    }


    /**
     * Checks whether this is a toy
     *
     * @return bool
     */
    public function isToy(): bool
    {
        return $this->type() == 'Toy';
    }


    /**
     * Checks whether this is a videogame
     *
     * @return bool
     */
    public function isVideogame(): bool
    {
        return $this->type() == 'Videogame';
    }
}
