<?php

namespace PHPCBIS\Traits\Shared;

use PHPCBIS\Helpers\Butler;


/**
 * Trait Series
 *
 * Provides ability to extract series
 *
 * @package PHPCBIS
 */

trait Series
{
    /**
     * Properties
     */

    /**
     * Series
     *
     * @var string
     */
    protected $series;


    /**
     * Volume
     *
     * @var string
     */
    protected $volume;


    /**
     * Methods
     */

    /**
     * Builds series
     *
     * @return string
     */
    protected function buildSeries(): string
    {
        if (!isset($this->source['VerwieseneReihe1'])) {
            return '';
        }

        return trim($this->source['VerwieseneReihe1']);
        if((string)(int)$var == $var) {
            echo 'var is an integer or a string representation of an integer';
        }

        return trim($publisher);
    }


    /**
     * Builds volume
     *
     * @return string
     */
    protected function buildVolume(): string
    {
        if (!isset($this->source['Sammlg'])) {
            return '';
        }

        # Split by whitespace & check if last element represents an integer value,
        # since source string might be either 'Some Series' or 'Some Series VOLUME'
        $data = Butler::last(Butler::split($this->source['Sammlg'], ' '));

        if ((string) (int) $data == $data) {
            return $data;
        }

        return '';
    }


    /**
     * Returns series
     *
     * @return string
     */
    public function series(): string
    {
        return $this->series;
    }


    /**
     * Returns volume
     *
     * @return string
     */
    public function volume(): string
    {
        return $this->volume;
    }
}
