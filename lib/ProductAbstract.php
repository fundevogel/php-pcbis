<?php

namespace PHPCBIS;


/**
 * Class BookList
 *
 * Serves as template for collections of books
 *
 * @package PHPCBIS
 */

abstract class ProductAbstract
{
    /**
     * Product identifiers indicating product type
     *
     * @var array
     */
    private $productGroups = [
        'AB' => 'Nonbook',
        'AC' => 'Hörbuch',
        'AD' => 'Film',
        'AE' => 'Software',
        'AF' => 'Tonträger',
        'AG' => 'ePublikation',
        'AH' => 'Games',
        'AI' => 'Kalender',
        'AJ' => 'Landkarte/Globus',
        'AK' => 'Musik',
        'AL' => 'Noten',
        'AM' => 'Papeterie/PBS',
        'AN' => 'Spiel',
        'AO' => 'Spielzeug',
        'HC' => 'Hardcover',
        'SB' => 'Schulbuch',
        'TB' => 'Taschenbuch',
    ];


    /**
     * Product type
     *
     * @var string
     */
    private $productGroup;


    public function __construct(array $source)
    {
        $this->productGroup = $this->productGroups[$source['Sortimentskennzeichen']];
    }
}
