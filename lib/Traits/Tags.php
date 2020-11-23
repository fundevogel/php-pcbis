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
     * @return array
     */
    protected function buildCategories(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        $categories = [];

        foreach ($this->tags as $tag) {
            $lowercase = Butler::lower($tag);

            if (Butler::contains($lowercase, 'bilderbuch')) {
                $categories[] = 'Bilderbuch';
            }

            if (Butler::contains($lowercase, 'vorlesebuch')) {
                $categories[] = 'Vorlesebuch';
            }

            if (Butler::contains($lowercase, 'sachbuch')) {
                $categories[] = 'Sachbuch';
            }
        }

        return array_unique($categories);
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

        // if (!empty($this->translations)) {
        //     $translations = $this->translations;
        // }

        // $topics = array_map(function ($topic) use ($translations) {
        //     # Add 'Antolin' rating if available ..
        //     if (Butler::startsWith($topic, 'Antolin')) {
        //         $string = Butler::replace($topic, ['(', ')'], '');

        //         # .. but not as topic
        //         $this->antolin = Butler::split($string, 'Antolin')[0];

        //         return '';
        //     }

        //     if (isset($translations[$topic])) {
        //         return $translations[$topic];
        //     }
        // }, $this->tags);

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
