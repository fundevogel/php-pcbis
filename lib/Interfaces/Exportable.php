<?php

namespace PHPCBIS\Interfaces;

use PHPCBIS\Helpers\Butler;


/**
 * Interface Exportable
 *
 * Defines requirements for exporting a complete dataset
 *
 * @package PHPCBIS
 */

Interface Exportable
{
    /**
     * Returns dataset
     */
    public function export(bool $asArray = false);
}
