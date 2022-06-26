<?php

namespace Fundevogel\Pcbis\Interfaces;

use Fundevogel\Pcbis\Helpers\Butler;


/**
 * Interface Taggable
 *
 * Defines requirements for poviding categories & topics
 */
interface Taggable
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
