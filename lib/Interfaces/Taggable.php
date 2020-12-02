<?php

namespace Pcbis\Interfaces;

use Pcbis\Helpers\Butler;


/**
 * Interface Taggable
 *
 * Defines requirements for poviding categories & topics
 *
 * @package PHPCBIS
 */

Interface Taggable
{
    /**
     * Returns categories
     */
    public function categories();


    /**
     * Returns topics
     */
    public function topics();
}
