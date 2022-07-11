<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Types\Media;

use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
use Fundevogel\Pcbis\Classes\Product\Types\Medium;

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
        if (!isset($this->data['AutorSachtitel'])) {
            return [];
        }

        $array = [
            ' DVD',
            ' Blu-ray',
        ];

        # Loop over suspicious strings ..
        foreach ($array as $string) {
            # .. and in case of a match ..
            if (Str::contains($this->data['AutorSachtitel'], $string)) {
                # .. reset author
                return [];
            }
        }

        return parent::buildAuthor();
    }


    /**
     * Exports recommended minimum age (in years)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function age(): Value
    {
        if (!isset($this->data['SonstTxt'])) {
            return new Value();
        }

        $age = '';

        if (preg_match('/FSK\s(.*)\sfreigegeben/', $this->data['SonstTxt'], $matches)) {
            $age = $matches[1] . ' Jahren';
        }

        return new Value($age);
    }


    /**
     * Dataset methods
     */

    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Movie' specific data
            'SchauspielerIn' => $this->getRole('actor')->value(),
        ]);
    }
}
