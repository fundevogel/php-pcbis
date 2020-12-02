<?php

namespace Pcbis\Products;

use Pcbis\Exceptions\UnknownTypeException;

use Pcbis\Products\Books\Types\Ebook;
use Pcbis\Products\Books\Types\Hardcover;
use Pcbis\Products\Books\Types\Schoolbook;
use Pcbis\Products\Books\Types\Softcover;

use Pcbis\Products\Media\Types\Audiobook;
use Pcbis\Products\Media\Types\Movie;
use Pcbis\Products\Media\Types\Music;
use Pcbis\Products\Media\Types\Sound;

use Pcbis\Products\Nonbook\Types\Boardgame;
use Pcbis\Products\Nonbook\Types\Calendar;
use Pcbis\Products\Nonbook\Types\Map;
use Pcbis\Products\Nonbook\Types\Nonbook;
use Pcbis\Products\Nonbook\Types\Notes;
use Pcbis\Products\Nonbook\Types\Software;
use Pcbis\Products\Nonbook\Types\Stationery;
use Pcbis\Products\Nonbook\Types\Toy;
use Pcbis\Products\Nonbook\Types\Videogame;


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
     * @throws \Pcbis\Exceptions\UnknownTypeException
     * @return \Pcbis\Product
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

                # Media
                case 'Film':
                    return new Movie($source, $props);
                case 'Hörbuch':
                    return new Audiobook($source, $props);
                case 'Musik':
                    return new Music($source, $props);
                case 'Tonträger':
                    return new Sound($source, $props);

                # Nonbook
                case 'Games':
                    return new Videogame($source, $props);
                case 'Kalender':
                    return new Calendar($source, $props);
                case 'Landkarte/Globus':
                    return new Map($source, $props);
                case 'Nonbook':
                    return new Nonbook($source, $props);
                case 'Noten':
                    return new Notes($source, $props);
                case 'Papeterie/PBS':
                    return new Stationery($source, $props);
                case 'Software':
                    return new Software($source, $props);
                case 'Spiel':
                    return new Boardgame($source, $props);
                case 'Spielzeug':
                    return new Toy($source, $props);
            }
        }

        throw new UnknownTypeException('Unknown type: "' . $groups[$group] . '"');
    }
}
