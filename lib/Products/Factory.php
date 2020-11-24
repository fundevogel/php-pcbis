<?php

namespace PHPCBIS\Products;

use PHPCBIS\Exceptions\UnknownTypeException;

use PHPCBIS\Products\Audio\Types\Audiobook;
use PHPCBIS\Products\Audio\Types\Music;
use PHPCBIS\Products\Audio\Types\Sound;
use PHPCBIS\Products\Books\Types\eBook;
use PHPCBIS\Products\Books\Types\Hardcover;
use PHPCBIS\Products\Books\Types\Schoolbook;
use PHPCBIS\Products\Books\Types\Softcover;


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

        # Default group (rarely)
        $group = 'HC';

        if (isset($source['Sortimentskennzeichen'])) {
            $group = $source['Sortimentskennzeichen'];
        }

        if (array_key_exists($group, $groups)) {
            $props['type'] = $groups[$group];

            switch ($groups[$group]) {
                # Books
                case 'ePublikation':
                    return new Ebook($source, $props);
                case 'Hardcover':
                    return new Hardcover($source, $props);
                case 'Schulbuch':
                    return new Schoolbook($source, $props);
                case 'Taschenbuch':
                    return new Softcover($source, $props);

                # Audio
                case 'Hörbuch':
                    return new Audiobook($source, $props);
                case 'Musik':
                    return new Music($source, $props);
                case 'Tonträger':
                    return new Sound($source, $props);
            }
        }

        # TODO: Extend product group support
        throw new UnknownTypeException('Unknown type: "' . $groups[$group] . '"');
    }
}
