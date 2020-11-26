<?php

namespace PHPCBIS\Products;

use PHPCBIS\Helpers\Butler;

use PHPCBIS\Interfaces\Sociable;
use PHPCBIS\Interfaces\Taggable;

use PHPCBIS\Traits\CheckType;
use PHPCBIS\Traits\DownloadCover;
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

    use CheckType;
    use People, Tags;


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
     * Translatable strings
     *
     * @var array
     */
    protected $translations;


    /**
     * Type of product
     *
     * @var string
     */
    protected $type;


    /**
     * Dataset properties
     */

    /**
     * Title
     *
     * @var string
     */
    protected $title;


    /**
     * Subtitle
     *
     * @var string
     */
    protected $subtitle;


    /**
     * Description
     *
     * @var string
     */
    protected $description;


    /**
     * Retail price (in €)
     *
     * @var string
     */
    protected $retailPrice;


    /**
     * Release year
     *
     * @var string
     */
    protected $releaseYear;


    /**
     * Minimum age recommendation (in years)
     *
     * @var string
     */
    protected $age;


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

        # Build basic dataset
        $this->title        = $this->buildTitle();
        $this->subtitle     = $this->buildSubtitle();
        $this->description  = $this->buildDescription();
        $this->retailPrice  = $this->buildretailPrice();
        $this->releaseYear  = $this->buildreleaseYear();
        $this->age          = $this->buildAge();

        # Build categories & topics from tags
        $this->categories   = $this->buildCategories();
        $this->topics       = $this->buildTopics();

        # Import translations
        $this->translations = $props['translations'];
    }


    /**
     * Magic methods
     */

    public function __toString(): string
    {
        if (empty($this->author)) {
            return $this->title();
        }

        return $this->author(true) . ': ' . $this->title();
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
     * Returns ISBN
     *
     * @return string
     */
    public function isbn(): string
    {
        return $this->isbn;
    }


    /**
     * Dataset methods
     */

    /**
     * Builds title
     *
     * @return string
     */
    protected function buildTitle(): string
    {
        if (!isset($this->source['Titel'])) {
            return '';
        }

        return $this->source['Titel'];
    }


    public function title(): string
    {
        return $this->title;
    }


    /**
     * Builds subtitle
     *
     * @return string
     */
    protected function buildSubtitle(): string
    {
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return '';
        }

        return $this->source['Utitel'];
    }


    public function subtitle(): string
    {
        return $this->subtitle;
    }


    /**
     * Builds description
     *
     * @return string
     */
    protected function buildDescription(): string
    {
        if (!isset($array['Text1'])) {
            return '';
        }

        $string = $array['Text1'];
        $description = Butler::split($string, 'º');

        foreach ($description as $index => $text) {
            $text = htmlspecialchars_decode($text);
            $text = Butler::replace($text, '<br><br>', '. ');
            $text = Butler::unhtml($text);
            $description[$index] = $text;

            if (Butler::length($description[$index]) < 130 && count($description) > 1) {
                unset($description[array_search($text, $description)]);
            }
        }

        return Butler::first($description);
    }

    public function description(): string
    {
        return $this->description;
    }


    /**
     * Builds retail price (in €)
     *
     * @return string
     */
    protected function buildRetailPrice(): string
    {
        // Input: XX(.YY)
        // Output: XX,YY
        if (!isset($this->source['PreisEurD'])) {
            return '';
        }

        $retailPrice = (float) $this->source['PreisEurD'];

        return number_format($retailPrice, 2, ',', '');
    }

    public function retailPrice(): string
    {
        return $this->retailPrice;
    }


    /**
     * Builds release year
     *
     * @return string
     */
    protected function buildReleaseYear(): string
    {
        if (!isset($this->source['Erschjahr'])) {
            return '';
        }

        return $this->source['Erschjahr'];
    }

    public function releaseYear(): string
    {
        return $this->releaseYear;
    }


    /**
     * Builds minimum age recommendation (in years)
     * TODO: Cater for months
     *
     * @return string
     */
    protected function buildAge(): string
    {
        if (!isset($this->source['Alter'])) {
            return '';
        }

        $age = Butler::substr($this->source['Alter'], 0, 2);

        if (Butler::substr($age, 0, 1) === '0') {
            $age = Butler::substr($age, 1, 1);
        }

      	return 'ab ' . $age . ' Jahren';
    }

    public function age(): string
    {
        return $this->age;
    }


    /**
     * Forces all (sub)classes to provide an easy way to export a full dataset
     */
    abstract protected function export(bool $asArray);
}
