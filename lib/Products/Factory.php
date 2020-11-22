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
     * @throws \PHPCBIS\Exceptions\UnknownTypeException
     * @return \PHPCBIS\Product
     */
    public static function factory(array $source, array $props): Product
    {
        $groups = [
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

        $group = $source['Sortimentskennzeichen'];

        if (array_key_exists($group, $groups)) {
            $props['type'] = $groups[$group];

            switch ($groups[$group]) {
                # Books
                case 'Hardcover':
                    return new \PHPCBIS\Products\Books\Types\Hardcover($source, $props);
                case 'Taschenbuch':
                    return new \PHPCBIS\Products\Books\Types\Softcover($source, $props);
                case 'Schulbuch':
                    return new \PHPCBIS\Products\Books\Types\Schoolbook($source, $props);
        }

        # TODO: Extend product group support
        throw new \PHPCBIS\Exceptions\UnknownTypeException('Unknown type: "' . $groups[$group] . '"');
    }
}
