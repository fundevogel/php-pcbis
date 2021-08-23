<?php

namespace Pcbis\Products\Media\Types;

use Pcbis\Products\Media\Medium;

use Pcbis\Helpers\Butler;
use Pcbis\Traits\Shared\Categories;
use Pcbis\Traits\Shared\Series;
use Pcbis\Traits\Shared\Topics;



/**
 * Class Movie
 *
 * KNV product category 'Film'
 *
 * @package PHPCBIS
 */

class Movie extends Medium {
    /**
     * Traits
     */

    use Categories, Topics;
    use Series;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->series    = $this->buildSeries();
        $this->volume    = $this->buildVolume();
    }


    /**
     * Overrides
     */

    /**
     * Builds minimum age recommendation (in years)
     * TODO: Cater for months
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
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Media' dataset
            parent::export($asArray), [
            # (2) 'Movie' specific data
            'Reihe'          => $this->series(),
            'Band'           => $this->volume(),
            'SchauspielerIn' => $this->getRole('actor', $asArray),
        ]);
    }
}
