<?php

namespace PHPCBIS\Products;

use PHPCBIS\Interfaces\Sociable;
use PHPCBIS\Interfaces\Taggable;

use PHPCBIS\Traits\People;
use PHPCBIS\Traits\Tags;


/**
 * Class Product
 *
 * Serves as template for products
 *
 * @package PHPCBIS
 */

abstract class Product implements Sociable, Taggable
{
    /**
     * Traits
     */

    use People;
    use Tags;


    /**
     * Properties
     */

    /**
     * International Standard Book Number
     *
     * @var string
     */
    protected $isbn;


    /**
     * Source data fetched from KNV's API
     *
     * @var array
     */
    protected $source;


    /**
     * Whether source data was fetched from cache
     *
     * @var bool
     */
    protected $fromCache;


    /**
     * Path to downloaded book cover images
     *
     * @var string
     */
    protected $imagePath;


    /**
     * Translatable strings
     *
     * @var array
     */
    protected $translations;


    /**
     * Involved people (all roles)
     *
     * @var array
     */
    protected $people;


    /**
     * Delimiter between people when exported as string
     *
     * @var string
     */
    protected $delimiter = '; ';


    /**
     * Tags (categories & topics)
     *
     * @var array
     */
    protected $tags;


    /**
     * Categories
     *
     * @var array
     */
    protected $categories;


    /**
     * Topics
     *
     * @var array
     */
    protected $topics;


    /**
     * Type of product
     *
     * @var string
     */
    protected $type;


    /**
     * Product group 'Audio'
     *
     * @var array
     */

    protected $audio = [
        'Hörbuch',
        'Tonträger',
        'Musik',
        # 'Noten',
    ];


    /**
     * Product group 'Book'
     *
     * @var array
     */

    protected $book = [
        'ePublikation',
        # 'Noten',
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
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        # Store source data, fetched from KNV's API ..
        $this->source = $source;

        # .. or from cache?
        $this->fromCache = $props['fromCache'];

        # Store valid ISBN
        $this->isbn = $props['isbn'];

        # Store product type
        $this->type = $props['type'];

        # Extract tags & involved people early on
        $this->tags         = $this->separateTags();
        $this->people       = $this->separatePeople();

        # Build categories & topics from tags
        $this->categories   = $this->buildCategories();
        $this->topics       = $this->buildTopics();

        # Import image path & translations
        $this->imagePath = $props['imagePath'];
        $this->translations = $props['translations'];
    }


    /**
     * Setters & getters
     */

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Methods
     */

    /**
     * Shows source data fetched from KNV's API
     *
     * @return array
     */
    public function showSource(): array
    {
        return $this->source;
    }


    /**
     * Checks whether source data was fetched from cache
     *
     * @return bool
     */
    public function fromCache(): bool
    {
        return $this->fromCache;
    }


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
     * Checks whether this is a nonbook
     *
     * @return bool
     */
    public function isNonbook(): bool
    {
        return in_array($this->type, $this->nonbook);
    }
}
