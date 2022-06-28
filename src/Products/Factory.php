<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products;

use Fundevogel\Pcbis\Exceptions\UnknownTypeException;
use Fundevogel\Pcbis\Products\Product;
use Fundevogel\Pcbis\Products\Books\Types\Ebook;
use Fundevogel\Pcbis\Products\Books\Types\Hardcover;
use Fundevogel\Pcbis\Products\Books\Types\Schoolbook;
use Fundevogel\Pcbis\Products\Books\Types\Softcover;
use Fundevogel\Pcbis\Products\Media\Types\Audiobook;
use Fundevogel\Pcbis\Products\Media\Types\Movie;
use Fundevogel\Pcbis\Products\Media\Types\Music;
use Fundevogel\Pcbis\Products\Media\Types\Sound;
use Fundevogel\Pcbis\Products\Nonbook\Types\Boardgame;
use Fundevogel\Pcbis\Products\Nonbook\Types\Calendar;
use Fundevogel\Pcbis\Products\Nonbook\Types\Map;
use Fundevogel\Pcbis\Products\Nonbook\Types\Nonbook;
use Fundevogel\Pcbis\Products\Nonbook\Types\Notes;
use Fundevogel\Pcbis\Products\Nonbook\Types\Software;
use Fundevogel\Pcbis\Products\Nonbook\Types\Stationery;
use Fundevogel\Pcbis\Products\Nonbook\Types\Toy;
use Fundevogel\Pcbis\Products\Nonbook\Types\Videogame;

/**
 * Class Factory
 *
 * Creates products - pretty much like a factory *duh*
 */
final class Factory
{
    /**
     * Available product types
     *
     * @var array
     */

    public static $types = [
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
     * Creates new product
     *
     * @param array $data Source data fetched from KNV's API
     * @param array $api Object granting access to KNV's API
     * @throws \Fundevogel\Pcbis\Exceptions\UnknownTypeException
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public static function factory(array $data, $api)
    {
        # Determine product type
        $type = $data['Sortimentskennzeichen'];

        if (!array_key_exists($type, static::$types)) {
            throw new UnknownTypeException(sprintf('Unknown type: "%s"', $type));
        }

        switch (static::$types[$type]) {
            # Books
            case 'ePublikation':
                return new Ebook($data, $api);
            case 'Hardcover':
                return new Hardcover($data, $api);
            case 'Schulbuch':
                return new Schoolbook($data, $api);
            case 'Taschenbuch':
                return new Softcover($data, $api);

            # Media
            case 'Film':
                return new Movie($data, $api);
            case 'Hörbuch':
                return new Audiobook($data, $api);
            case 'Musik':
                return new Music($data, $api);
            case 'Tonträger':
                return new Sound($data, $api);

            # Nonbook
            case 'Games':
                return new Videogame($data, $api);
            case 'Kalender':
                return new Calendar($data, $api);
            case 'Landkarte/Globus':
                return new Map($data, $api);
            case 'Nonbook':
                return new Nonbook($data, $api);
            case 'Noten':
                return new Notes($data, $api);
            case 'Papeterie/PBS':
                return new Stationery($data, $api);
            case 'Software':
                return new Software($data, $api);
            case 'Spiel':
                return new Boardgame($data, $api);
            case 'Spielzeug':
                return new Toy($data, $api);
        }
    }
}
