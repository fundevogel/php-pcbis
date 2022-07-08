<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Interfaces;

/**
 * Interface Product
 *
 * Defines implementation of single products
 */
interface Product
{
    /**
     * Exports European Article Number (EAN)
     *
     * @return string
     */
    public function ean(): string;


    /**
     * Exports title
     *
     * @return string
     */
    public function title(): string;


    /**
     * Exports subtitle
     *
     * @return string
     */
    public function subtitle(): string;


    /**
     * Exports retail price (in €)
     *
     * @return string
     */
    public function retailPrice(): string;


    /**
     * Exports release year
     *
     * @return string
     */
    public function releaseYear(): string;


    /**
     * Exports type of value added tax (VAT)
     *
     * @return string
     */
    public function vat(): string;


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array;
}
