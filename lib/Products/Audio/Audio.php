<?php

namespace PHPCBIS\Products\Audio;

use PHPCBIS\Interfaces\Taggable;
use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Product;


/**
 * Class Audio
 *
 * @package PHPCBIS
 */

class Audio extends Product implements Taggable
{
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

        # Build dataset
        // $this->author       = $this->buildAuthor();
        // $this->title        = $this->buildTitle();
        // $this->subtitle     = $this->buildSubtitle();
        // $this->publisher    = $this->buildPublisher();
        // $this->description  = $this->buildDescription();
        // $this->retailPrice  = $this->buildretailPrice();
        // $this->releaseYear  = $this->buildreleaseYear();
        // $this->age          = $this->buildAge();

        # Build involved people
        $this->illustrator  = $this->getRole('illustrator', true);
        $this->drawer       = $this->getRole('drawer', true);
        $this->photographer = $this->getRole('photographer', true);
        $this->translator   = $this->getRole('translator', true);
        $this->editor       = $this->getRole('editor', true);
        $this->narrator     = $this->getRole('narrator', true);
        $this->director     = $this->getRole('director', true);
        $this->producer     = $this->getRole('producer', true);
        $this->participant  = $this->getRole('participant');
    }


    /**
     * Magic methods
     */

    public function __toString(): string
    {
        if (empty($this->author)) {
            return $this->getTitle();
        }

        return $this->getAuthor(true) . ': ' . $this->getTitle();
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
        if (!isset($this->source['Utitel']) || !$this->isAudiobook()) {
            return '';
        }

        $string = $this->source['Utitel'];
        $array = Butler::split($string, '.');

        return Butler::replace(Butler::last($array), ' Min', '');
    }

    public function setDuration(string $duration)
    {
        $this->duration = $duration;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }
}
