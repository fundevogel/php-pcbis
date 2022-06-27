<?php

namespace Fundevogel\Pcbis\Products\Nonbook\Types;

use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Products\Nonbook\Item;

/**
 * Class Videogame
 *
 * KNV product category 'Games'
 */
class Videogame extends Item
{
    /**
     * Properties
     */

    /**
     * Supported platforms
     *
     * @var array
     */
    protected $platforms;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->platforms = $this->buildPlatforms();
    }


    /**
     * Methods
     */

    /**
     * Builds supported platforms
     *
     * @return array
     */
    protected function buildPlatforms(): array
    {
        if (!isset($this->source['AutorSachtitel'])) {
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
            if (Str::contains($this->source['AutorSachtitel'], $key)) {
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

        return $platforms;
    }


    /**
     * Exports supported platforms
     *
     * @param bool $asArray Whether to export an array (rather than a string)
     * @return string|array
     */
    public function platforms(bool $asArray = false)
    {
        if ($asArray) {
            return $this->platforms;
        }

        return A::join($this->platforms, '; ');
    }


    /**
     * Overrides
     */

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

        if (preg_match('/USK\s(.*)\sfreigegeben/', $this->source['SonstTxt'], $matches)) {
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
            # (1) 'Item' dataset
            parent::export($asArray),
            [
                # (2) 'Videogame' specific data
                'Plattformen' => $this->platforms($asArray),
            ]
        );
    }
}
