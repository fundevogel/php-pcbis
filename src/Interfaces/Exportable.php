<?php

namespace Fundevogel\Pcbis\Interfaces;


/**
 * Interface Exportable
 *
 * Defines requirements for exporting a complete dataset
 */
interface Exportable
{
    /**
     * Exports dataset
     */
    public function export(bool $asArray = false);
}
