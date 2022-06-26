<?php

namespace Fundevogel\Pcbis\Interfaces;


/**
 * Interface Sociable
 *
 * Defines requirements for involving people of different roles
 */
interface Sociable
{
    /**
     * Exports people of given role
     */
    public function getRole(string $role, bool $asArray = false);


    /**
     * Sets & gets delimiter when exporting involved people as string
     */
    public function setDelimiter(string $delimiter);
    public function getDelimiter();
}
