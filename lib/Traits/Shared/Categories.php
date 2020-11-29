<?php

namespace PHPCBIS\Traits\Shared;

use PHPCBIS\Helpers\Butler;


/**
 * Trait Categories
 *
 * Provides ability to extract categories (book/audiobook)
 *
 * @package PHPCBIS
 */

trait Categories
{
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
}
