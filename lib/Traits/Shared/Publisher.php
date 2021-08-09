<?php

namespace Pcbis\Traits\Shared;

use Pcbis\Helpers\Butler;


/**
 * Trait Publisher
 *
 * Provides ability to extract publisher (book/audiobook/calendar)
 *
 * @package PHPCBIS
 */

trait Publisher
{
    /**
     * Properties
     */

    /**
     * Publisher
     *
     * @var string
     */
    protected $publisher;


    /**
     * Methods
     */

    /**
     * Builds publisher
     *
     * @return string
     */
    protected function buildPublisher(): string
    {
        if (!isset($this->source['IndexVerlag'])) {
            return '';
        }

        $publisher = $this->source['IndexVerlag'];

        if (is_array($publisher)) {
            $publisher = Butler::first($publisher);
        }

        return trim($publisher);
    }


    /**
     * Returns publisher
     *
     * @return string
     */
    public function publisher(): string
    {
        return $this->publisher;
    }
}
