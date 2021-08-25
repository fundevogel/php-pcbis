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
        if (isset($this->source['VerwieseneReihe2'])) {
            return trim($this->source['VerwieseneReihe2']);
        }

        if (isset($this->source['VerwieseneReihe1'])) {
            return trim($this->source['VerwieseneReihe1']);
        }

        return '';
    }


    /**
     * Builds volume
     *
     * @return string
     */
    protected function buildVolume(): string
    {
        if (isset($this->source['BandnrVerwieseneReihe2'])) {
            return $this->source['BandnrVerwieseneReihe2'];
        }

        if (isset($this->source['BandnrVerwieseneReihe1'])) {
            return $this->source['BandnrVerwieseneReihe1'];
        }

        return '';
    }


    /**
     * Whether product is part of a series
     *
     * @return string
     */
    public function isSeries(): string
    {
        return $this->series !== '';
    }


    /**
     * Exports series
     *
     * @return string
     */
    public function series(): string
    {
        return $this->series;
    }


    /**
     * Exports volume
     *
     * @return string
     */
    public function volume(): string
    {
        return $this->volume;
    }
}
