<?php

namespace Pcbis\Traits;

use Pcbis\Helpers\Butler;


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
     * @return array
     */
    protected function buildCategories(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        $categories = [];

        if ($this->isAudiobook()) {
            $categories[] = 'Hörbuch';
        }

        foreach ($this->tags as $tag) {
            $tag = trim($tag);

            # High(er) accuracy
            if (in_array($tag, ['Kinderbuch', 'Jugendbuch'])) {
                $categories[] = $tag;
            }

            # 'Erstlesebuch', 'Erstlesesachbuch'
            if (Butler::startsWith($tag, 'Erstlese')) {
                $categories[] = 'Erstlesebuch';
            }

            # 'Vorlesebuch', 'Vorlesen'
            if (Butler::startsWith($tag, 'Vorlese')) {
                $categories[] = 'Vorlesebuch';
            }

            # Low(er) accuracy
            $lowercase = Butler::lower($tag);

            # 'Kindersachbuch', 'Jugendsachbuch', 'Erstlesesachbuch' || 'Sach-Bilderbuch', 'Sachbilderbuch'
            if (Butler::contains($lowercase, 'sachbuch') || in_array($tag, ['Sach-Bilderbuch', 'Sachbilderbuch'])) {
                $categories[] = 'Sachbuch';
            }

            # 'Kunst-Bilderbuch', 'Fühl-Bilderbuch', 'Märchen-Bilderbuch'
            if (Butler::contains($lowercase, 'bilderbuch')) {
                $categories[] = 'Bilderbuch';
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
        # Store blocked topics
        $blockList = [
            # Rather categories than topics
            'Hörbuch',
            'Papp-Bilderbuch',
            'Umwelt-Bilderbuch',
            'Vorlesebuch',

            # Highly sophisticated ways to say 'book for kids'
            # (1) Non-fiction for kids
            'Kinder-/Jugendsachbuch',
            'Kindersachbuch/Jugendsachbuch',
            'Kindersachbuch/Jugendsachbuch.',
            # (2) Literature for children & adolescents
            'Kinderliteratur/Jugendliteratur',
            'Kinder-/Jugendliteratur',
            'Kinder/Jugendliteratur',
            'Kinder-/Jugendlit.',
            'Kinder/Jugendlit.',
        ];

        $topics = array_map(function ($topic) use ($blockList) {
            # Skip blocklisted topics
            if (in_array($topic, $blockList)) {
                return '';
            }

            # Skip 'Antolin' rating
            if (Butler::startsWith($topic, 'Antolin')) {
                return '';
            }

            return $topic;
        }, $this->tags);

        return array_filter($topics);
    }


    private function exportProperty(array $property, bool $asArray, string $delimiter)
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
