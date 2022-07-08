<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product;

use Fundevogel\Pcbis\Exceptions\UnknownTypeException;
use Fundevogel\Pcbis\Interfaces\Product;
use Fundevogel\Pcbis\Classes\Product\Books\Types\Ebook;
use Fundevogel\Pcbis\Classes\Product\Books\Types\Hardcover;
use Fundevogel\Pcbis\Classes\Product\Books\Types\Schoolbook;
use Fundevogel\Pcbis\Classes\Product\Books\Types\Softcover;
use Fundevogel\Pcbis\Classes\Product\Media\Types\Audiobook;
use Fundevogel\Pcbis\Classes\Product\Media\Types\Movie;
use Fundevogel\Pcbis\Classes\Product\Media\Types\Music;
use Fundevogel\Pcbis\Classes\Product\Media\Types\Sound;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Boardgame;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Calendar;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Map;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Nonbook;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Notes;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Software;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Stationery;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Toy;
use Fundevogel\Pcbis\Classes\Product\Nonbook\Types\Videogame;

/**
 * Class Factory
 *
 * Creates single products (factory-style)
 */
class Factory
{
    /**
     * Available product types
     *
     * @var array
     */
    public static $types = [
        # (1) Books
        'AG' => 'ePublikation',
        'HC' => 'Hardcover',
        'SB' => 'Schulbuch',
        'TB' => 'Taschenbuch',

        # (2) Media
        'AC' => 'Hörbuch',
        'AD' => 'Film',
        'AF' => 'Tonträger',
        'AK' => 'Musik',

        # (3) Nonbook
        'AB' => 'Nonbook',
        'AE' => 'Software',
        'AH' => 'Games',
        'AI' => 'Kalender',
        'AJ' => 'Landkarte/Globus',
        'AL' => 'Noten',
        'AM' => 'Papeterie/PBS',
        'AN' => 'Spiel',
        'AO' => 'Spielzeug',
    ];


    /**
     * Creates 'Product' instance matching given type
     *
     * @param array $data Raw product data
     * @throws \Fundevogel\Pcbis\Exceptions\UnknownTypeException
     * @return \Fundevogel\Pcbis\Interfaces\Product
     */
    public static function create(array $data): Product
    {
        # Determine Product type identifier
        $type = $data['Sortimentskennzeichen'];

        # If product type is unknown ..
        if (!array_key_exists($type, static::$types)) {
            # .. fail early
            throw new UnknownTypeException(sprintf('Unknown type identifier: "%s"', $type));
        }

        # Create instance based on product type
        switch (static::$types[$type]) {
            # Books
            case 'ePublikation':
                return new Ebook($data);
            case 'Hardcover':
                return new Hardcover($data);
            case 'Schulbuch':
                return new Schoolbook($data);
            case 'Taschenbuch':
                return new Softcover($data);

            # Media
            case 'Film':
                return new Movie($data);
            case 'Hörbuch':
                return new Audiobook($data);
            case 'Musik':
                return new Music($data);
            case 'Tonträger':
                return new Sound($data);

            # Nonbook
            case 'Games':
                return new Videogame($data);
            case 'Kalender':
                return new Calendar($data);
            case 'Landkarte/Globus':
                return new Map($data);
            case 'Nonbook':
                return new Nonbook($data);
            case 'Noten':
                return new Notes($data);
            case 'Papeterie/PBS':
                return new Stationery($data);
            case 'Software':
                return new Software($data);
            case 'Spiel':
                return new Boardgame($data);
            case 'Spielzeug':
                return new Toy($data);
        }
    }
}
