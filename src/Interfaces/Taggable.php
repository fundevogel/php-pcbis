<?php

namespace Fundevogel\Pcbis\Interfaces;


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
