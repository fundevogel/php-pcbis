<?php

namespace PHPCBIS\Products;


/**
 * Class Product
 *
 * Serves as template for products
 *
 * @package PHPCBIS
 */

abstract class Product
{
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
     * Whether it's an audiobook
     *
     * @var bool
     */
    protected $isAudiobook = false;


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

        # Determine if audiobook
        if ($props['type'] === 'Hörbuch') {
            $this->isAudiobook = true;
        }

        # Import image path & translations
        $this->imagePath = $props['imagePath'];
        $this->translations = $props['translations'];
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
     * Checks whether this is an audiobook
     *
     * @return bool
     */
    public function isAudiobook(): bool
    {
        return $this->isAudiobook;
    }
}
