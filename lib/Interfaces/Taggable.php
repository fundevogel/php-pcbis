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
     * Exports categories
     */
    public function categories();


    /**
     * Exports topics
     */
    public function topics();
}
