<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Types\Nonbook;

use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
use Fundevogel\Pcbis\Classes\Product\Types\Item;

/**
 * Class Videogame
 *
 * KNV product category 'Games'
 */
class Videogame extends Item
{
    /**
     * Overrides
     */

    /**
     * Exports recommended minimum age (in years)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function age(): Value
    {
        if (isset($this->data['SonstTxt']) && preg_match('/USK\s(.*)\sfreigegeben/', $this->data['SonstTxt'], $matches)) {
            return new Value($matches[1] . ' Jahren');
        }

        return parent::age();
    }


    /**
     * Dataset methods
     */

    /**
     * Exports supported platforms
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function platforms(): Value
    {
        if (!isset($this->data['AutorSachtitel'])) {
            return [];
        }

        $array = [
            # Sony PlayStation
            'PS3-Blu-ray'        => 'PlayStation 3',
            'PS4-Blu-ray'        => 'PlayStation 4',
            'PS5-Blu-ray'        => 'PlayStation 5',

            # Microsoft Xbox
            'Xbox360'            => 'Xbox 360',
            'Xbox One'           => 'Xbox One',
            'Xbox Series X'      => 'Xbox Series X',

            # Nintendo
            'Nintendo Switch'    => 'Nintendo Switch',
            'Nintendo Wii U'     => 'Nintendo Wii U',
            'Nintendo-Wii-Spiel' => 'Nintendo Wii',

            # Other OSes
            'DVD-ROM'         => 'PC',
        ];

        $platforms = [];

        # Detect platforms
        # (1) Check for console gaming
        foreach ($array as $key => $value) {
            if (Str::contains($this->data['AutorSachtitel'], $key)) {
                $platforms[] = $value;
            }
        }

        # (2) Check for macOS/Linux & other operating systems
        if (empty($platforms)) {
            # TODO: KNV doesn't support those yet - there's NO way to detect OS even for games that support them
            # See
            # - '4020628755560'
            # - '4260252082049'
        }

        return new Value($platforms);
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Videogame' specific data
            'Plattformen' => $this->platforms()->value(),
        ]);
    }
}
