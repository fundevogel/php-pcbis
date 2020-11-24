<?php

namespace PHPCBIS\Traits;


/**
 * Trait CheckType
 *
 * Provides ability to determine product type
 *
 * @package PHPCBIS
 */

trait CheckType
{
    /**
     * Properties
     */

    /**
     * Product group 'Audio'
     *
     * @var array
     */

    protected $audio = [
        'Hörbuch',
        'Tonträger',
        'Musik',
    ];


    /**
     * Product group 'Book'
     *
     * @var array
     */

    protected $book = [
        'ePublikation',
        'Hardcover',
        'Schulbuch',
        'Taschenbuch',
    ];


    /**
     * Product group 'Nonbook'
     *
     * @var array
     */

    protected $nonbook = [
        'Nonbook',
        'Software',
        'Games',
        'Kalender',
        'Landkarte/Globus',
        'Noten',
        'Papeterie/PBS',
        'Spiel',
        'Spielzeug',
    ];


    /**
     * Methods
     */

    /**
     * Checks whether this is an audio
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return in_array($this->type, $this->audio);
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
     * Checks whether this is a nonbook
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return in_array($this->type, $this->nonbook);
    }
}
