<?php

namespace PHPCBIS\Products;


/**
 * Class ProductFactory
 *
 * Creates products - pretty much like a factory *duh*
 *
 * @package PHPCBIS
 */

final class Factory
{
    /**
     * Creates new product
     *
     * @param array $source - Source data fetched from KNV's API
     * @param array $props - Properties being passed to product
     * @return \PHPCBIS\Product
     */
    public static function factory(array $source, array $props): Product
    {
        $productGroups = [
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
        ];

        $books = [
            'HC' => 'Hardcover',
            'SB' => 'Schulbuch',
            'TB' => 'Taschenbuch',
        ];

        $productGroup = $source['Sortimentskennzeichen'];

        if (array_key_exists($productGroup, $books)) {
            $props['type'] = $books[$productGroup];

            return new \PHPCBIS\Products\Books\Book($source, $props);
        } elseif ($productGroup === 'AC') {
            $props['type'] = 'Hörbuch';

            return new \PHPCBIS\Products\Books\Book($source, $props);
            # TODO: Add audiobook subclass
            # return new \PHPCBIS\Products\Books\Audiobook($source, $props);
        }

        throw new \InvalidArgumentException('Unknown format given');
    }
}
