<?php

namespace PHPCBIS\Traits;

use PHPCBIS\Helpers\Butler;


/**
 * Trait Tags
 *
 * Provides ability to extract tags and build categories & topics
 *
 * @package PHPCBIS
 */

trait Tags
{
    /**
     * Properties
     */

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
     * Methods
     */

    /**
     * Extracts tags from source array
     *
     * @return array
     */
    protected function separateTags(): array
    {
        if (!isset($this->source['IndexSchlagw'])) {
            return [];
        }

        $data = $this->source['IndexSchlagw'];

        if (is_string($data)) {
            $data = Butler::split(trim($data), ';');
        }

        $tags = [];

        foreach ($data as $string) {
            $tags = array_merge($tags, Butler::split(trim($string), ';'));
        }

        return $tags;
    }


    /**
     * Builds categories
     *
     * For now, this doesn't do much.
     * An example implementation can be found in Products » Books » Book
     *
     * @return array
     */
    protected function buildCategories(): array
    {
        return [];
    }


    /**
     * Builds topics
     *
     * @return array
     */
    protected function buildTopics(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        return array_unique($this->tags);
    }


    protected function exportProperty(array $property, bool $asArray, string $delimiter)
    {
        if ($asArray) {
            return $property;
        }

        return Butler::join($property, $delimiter);
    }


    public function categories(bool $asArray = false, string $delimiter = ', ')
    {
        return $this->exportProperty($this->categories, $asArray, $delimiter);
    }


    public function topics(bool $asArray = false, string $delimiter = ', ')
    {
        return $this->exportProperty($this->topics, $asArray, $delimiter);
    }
}
