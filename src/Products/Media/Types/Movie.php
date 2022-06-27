<?php

namespace Fundevogel\Pcbis\Products\Media\Types;

use Fundevogel\Pcbis\Products\Media\Medium;

/**
 * Class Movie
 *
 * KNV product category 'Film'
 */
class Movie extends Medium
{
    /**
     * Overrides
     */

    /**
     * Builds author(s)
     *
     * @return array
     */
    protected function buildAuthor(): array
    {
        if (!isset($this->source['AutorSachtitel'])) {
            return [];
        }

        $array = [
            ' DVD',
            ' Blu-ray',
        ];

        # Loop over suspicious strings ..
        foreach ($array as $string) {
            # .. and in case of a match ..
            if (Str::contains($this->source['AutorSachtitel'], $string)) {
                # .. reset author
                return [];
            }
        }

        return parent::buildAuthor();
    }


    /**
     * Builds minimum age recommendation (in years)
     *
     * @return string
     */
    protected function buildAge(): string
    {
        if (!isset($this->source['SonstTxt'])) {
            return '';
        }

        $age = '';

        if (preg_match('/FSK\s(.*)\sfreigegeben/', $this->source['SonstTxt'], $matches)) {
            $age = $matches[1] . ' Jahren';
        }

        return $age;
    }


    /**
     * Exports all data
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Medium' dataset
            parent::export($asArray),
            [
                # (2) 'Movie' specific data
                'SchauspielerIn' => $this->getRole('actor', $asArray),
            ]
        );
    }
}
