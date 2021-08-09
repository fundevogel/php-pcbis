<?php

namespace Pcbis\Traits\Shared;

use Pcbis\Helpers\Butler;


/**
 * Trait Dimensions
 *
 * Provides ability to extract dimensions (book/calendar)
 *
 * @package PHPCBIS
 */

trait Dimensions
{
    /**
     * Properties
     */

    /**
     * Dimensions (width x height in centimeters)
     *
     * @var string
     */
    protected $dimensions;


    /**
     * Methods
     */

    /**
     * Builds dimensions (width x height)
     *
     * @return string
     */
    protected function buildDimensions(): string
    {
        # Width & height are either both present, or not at all
        if (!isset($this->source['Breite'])) {
            $delimiter = ' cm';

            # If they aren't though, check 'Abb' for further hints on dimensions
            if (isset($this->source['Abb']) && Butler::contains($this->source['Abb'], $delimiter)) {
                $string = Butler::replace($this->source['Abb'], $delimiter, '');
                $array = Butler::split($string, ' ');

                return Butler::convertMM(Butler::last($array));
            }

            return '';
        }

        $width = Butler::convertMM($this->source['Breite']);
        $height = Butler::convertMM($this->source['Hoehe']);

        return $width . ' x ' . $height;
    }


    /**
     * Exports dimensions
     *
     * @return string
     */
    public function dimensions(): string
    {
        return $this->dimensions;
    }
}
