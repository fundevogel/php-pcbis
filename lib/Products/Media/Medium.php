<?php

namespace PHPCBIS\Products\Media;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Product;

use PHPCBIS\Traits\DownloadCover;


/**
 * Class Medium
 *
 * @package PHPCBIS
 */

class Medium extends Product
{
    /**
     * Traits
     */

    use DownloadCover;


    /**
     * Properties
     */

    /**
     * Narrator
     *
     * @var array
     */
    protected $narrator;


    /**
     * Director
     *
     * @var array
     */
    protected $director;


    /**
     * Producer
     *
     * @var array
     */
    protected $producer;


    /**
     * Duration (in minutes)
     *
     * @var string
     */
    protected $duration;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Extend dataset
        $this->duration = $this->buildDuration();

        # Build involved people
        $this->narrator = $this->getRole('narrator', true);
        $this->director = $this->getRole('director', true);
        $this->producer = $this->getRole('producer', true);
    }


    /**
     * Methods
     */

    /**
     * Builds duration
     *
     * @return string
     */
    protected function buildDuration(): string
    {
        if (!isset($this->source['Utitel'])) {
            return '';
        }

        $array = Butler::split($this->source['Utitel'], '.');

        return Butler::replace(Butler::last($array), ' Min', '');
    }


    public function duration(): string
    {
        return $this->duration;
    }
}
