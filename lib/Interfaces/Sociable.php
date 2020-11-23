<?php

namespace PHPCBIS\Interfaces;

use PHPCBIS\Helpers\Butler;


/**
 * Interface Sociable
 *
 * Defines requirements for involving people of different roles
 *
 * @package PHPCBIS
 */

Interface Sociable
{
    /**
     * Returns people of given role
     */
    public function getRole(string $role, bool $asArray = false);


    /**
     * Sets & gets delimiter when exporting involved people as string
     */
    public function setDelimiter(string $delimiter);
    public function getDelimiter();
}
