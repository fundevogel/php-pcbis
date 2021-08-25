<?php

namespace Pcbis\Traits;

use Pcbis\Helpers\Butler;


/**
 * Trait Series
 *
 * Provides ability to extract series & volume therein
 *
 * @package PHPCBIS
 */

trait Series
{
    /**
     * Properties
     */

    /**
     * Series & volumes
     *
     * @var array
     */
    protected $series;


    /**
     * Methods
     */

    /**
     * Builds series
     *
     * @return array
     */
    protected function buildSeries(): array
    {
        $array = [
            'VerwieseneReihe1' => 'BandnrVerwieseneReihe1',
            'VerwieseneReihe2' => 'BandnrVerwieseneReihe2',
            'VerwieseneReihe3' => 'BandnrVerwieseneReihe3',
            'VerwieseneReihe4' => 'BandnrVerwieseneReihe4',
            'VerwieseneReihe5' => 'BandnrVerwieseneReihe5',
            'VerwieseneReihe6' => 'BandnrVerwieseneReihe6',
        ];

        $series = [];

        foreach ($array as $key => $value) {
            if (isset($this->source[$key]) && isset($this->source[$value])) {
                $series[trim($this->source[$key])] = trim($this->source[$value]);
            }
        }

        return $series;
    }


    /**
     * Whether product is part of a series
     *
     * @return bool
     */
    public function isSeries(): bool
    {
        return $this->series !== [];
    }


    /**
     * Exports series
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return string|array
     */
    public function series(bool $asArray = false)
    {
        if (empty($this->series)) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return array_keys($this->series);
        }

        return Butler::first(array_keys($this->series));
    }


    /**
     * Exports volume(s)
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return string|array
     */
    public function volume(bool $asArray = false)
    {
        if (empty($this->series)) {
            return $asArray ? [] : '';
        }

        if ($asArray) {
            return array_values($this->series);
        }

        return Butler::first(array_values($this->series));
    }
}
