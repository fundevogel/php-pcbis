<?php

namespace Fundevogel\Pcbis\Traits;

use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;


/**
 * Trait Tags
 *
 * Provides ability to extract tags and build categories & topics
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
            $data = Str::split(trim($data), ';');
        }

        $tags = [];

        foreach ($data as $string) {
            $tags = array_merge($tags, Str::split(trim($string), ';'));
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
            if (Str::startsWith($tag, 'Erstlese')) {
                $categories[] = 'Erstlesebuch';
            }

            # 'Vorlesebuch', 'Vorlesen'
            if (Str::startsWith($tag, 'Vorlese')) {
                $categories[] = 'Vorlesebuch';
            }

            # Low(er) accuracy
            $lowercase = Str::lower($tag);

            # 'Kindersachbuch', 'Jugendsachbuch', 'Erstlesesachbuch' || 'Sach-Bilderbuch', 'Sachbilderbuch'
            if (Str::contains($lowercase, 'sachbuch') || in_array($tag, ['Sach-Bilderbuch', 'Sachbilderbuch'])) {
                $categories[] = 'Sachbuch';
            }

            # 'Kunst-Bilderbuch', 'Fühl-Bilderbuch', 'Märchen-Bilderbuch'
            if (Str::contains($lowercase, 'bilderbuch')) {
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
            if (Str::startsWith($topic, 'Antolin')) {
                return '';
            }

            return $topic;
        }, $this->tags);

        return array_filter($topics);
    }


    /**
     * Exports tag
     *
     * @return array|string
     */
    private function exportTag($property, bool $asArray, string $delimiter): array|string
    {
        if ($asArray) {
            return $property;
        }

        return A::join($property, $delimiter);
    }


    /**
     * Exports categories
     *
     * @return array|string
     */
    public function categories(bool $asArray = false, string $delimiter = ', '): array|string
    {
        return $this->exportTag($this->categories, $asArray, $delimiter);
    }


    /**
     * Exports topics
     *
     * @return array|string
     */
    public function topics(bool $asArray = false, string $delimiter = ', '): array|string
    {
        return $this->exportTag($this->topics, $asArray, $delimiter);
    }
}
