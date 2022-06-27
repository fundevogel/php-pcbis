<?php

namespace Fundevogel\Pcbis\Traits;

/**
 * Trait Type
 *
 * Provides ability to determine product type
 */
trait Type
{
    /**
     * Properties
     */

    /**
     * Type of product
     *
     * @var string
     */
    protected $type;


    /**
     * Product group 'Book'
     *
     * @var array
     */

    private $book = [
        'ePublikation',
        'Hardcover',
        'Schulbuch',
        'Taschenbuch',
    ];


    /**
     * Product group 'Media'
     *
     * @var array
     */

    private $media = [
        'Film',
        'Hörbuch',
        'Musik',
        'Tonträger',
    ];


    /**
     * Product group 'Nonbook'
     *
     * @var array
     */

    private $nonbook = [
        'Games',
        'Kalender',
        'Landkarte/Globus',
        'Nonbook',
        'Noten',
        'Papeterie/PBS',
        'Software',
        'Spiel',
        'Spielzeug',
    ];


    /**
     * Setters & getters
     */

    /**
     * Exports product type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }


    /**
     * Methods
     */

    /**
     * Checks whether this is a book
     *
     * @return bool
     */
    public function isBook(): bool
    {
        return in_array($this->type, $this->book);
    }


    /**
     * Checks whether this is an eBook
     *
     * @return bool
     */
    public function isEbook(): bool
    {
        return $this->type === 'ePublikation';
    }


    /**
     * Checks whether this is a hardcover book
     *
     * @return bool
     */
    public function isHardcover(): bool
    {
        return $this->type === 'Hardcover';
    }


    /**
     * Checks whether this is a schoolbook
     *
     * @return bool
     */
    public function isSchoolbook(): bool
    {
        return $this->type === 'Schulbuch';
    }


    /**
     * Checks whether this is a softcover book
     *
     * @return bool
     */
    public function isSoftcover(): bool
    {
        return $this->type === 'Taschenbuch';
    }


    /**
     * Checks whether this is a medium
     *
     * @return bool
     */
    public function isMedia(): bool
    {
        return in_array($this->type, $this->media);
    }


    /**
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->type === 'Hörbuch';
    }


    /**
     * Checks whether this is a movie
     *
     * @return bool
     */
    public function isMovie(): bool
    {
        return $this->type === 'Film';
    }


    /**
     * Checks whether this is music
     *
     * @return bool
     */
    public function isMusic(): bool
    {
        return $this->type === 'Musik';
    }


    /**
     * Checks whether this is a sound storage medium
     *
     * @return bool
     */
    public function isSound(): bool
    {
        return $this->type === 'Tonträger';
    }


    /**
     * Checks whether this is a nonbook
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return in_array($this->type, $this->nonbook);
    }


    /**
     * Checks whether this is a boardgame
     *
     * @return bool
     */
    public function isBoardgame(): bool
    {
        return $this->type === 'Spiel';
    }


    /**
     * Checks whether this is a calendar
     *
     * @return bool
     */
    public function isCalendar(): bool
    {
        return $this->type === 'Kalender';
    }


    /**
     * Checks whether this is a map
     *
     * @return bool
     */
    public function isMap(): bool
    {
        return $this->type === 'Landkarte/Globus';
    }


    /**
     * Checks whether this is a generic item
     *
     * @return bool
     */
    public function isItem(): bool
    {
        return $this->type === 'Nonbook';
    }


    /**
     * Checks whether these are notes
     *
     * @return bool
     */
    public function isNotes(): bool
    {
        return $this->type === 'Noten';
    }


    /**
     * Checks whether this is software
     *
     * @return bool
     */
    public function isSoftware(): bool
    {
        return $this->type === 'Software';
    }


    /**
     * Checks whether this is stationery
     *
     * @return bool
     */
    public function isStationery(): bool
    {
        return $this->type === 'Papeterie/PBS';
    }


    /**
     * Checks whether this is a toy
     *
     * @return bool
     */
    public function isToy(): bool
    {
        return $this->type === 'Spielzeug';
    }


    /**
     * Checks whether this is a videogame
     *
     * @return bool
     */
    public function isVideogame(): bool
    {
        return $this->type === 'Games';
    }
}
