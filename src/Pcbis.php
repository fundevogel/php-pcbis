<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 3.0.0
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Webservice;
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
 * Class Pcbis
 *
 * Base class for everything pcbis.de
 */
final class Pcbis
{
    /**
     * Properties
     */

    /**
     * Webservice API client
     *
     * @var \Fundevogel\Pcbis\Api\Webservice
     */
    public Webservice $api;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @param string $cache Cache object
     * @return void
     */
    public function __construct(?array $credentials = null, public mixed $cache = null)
    {
        $this->api = new Webservice($credentials);
    }


    /**
     * Formats query results (helper function)
     *
     * @param string $identifier Product EAN/ISBN
     * @return array Matched products
     */
    private function _fetch(string $identifier): array
    {
        # Query API for matching search items
        $result = $this->api->suche($identifier);

        if ($result->suchenAntwort->gesamtTreffer > 0) {
            # Create data array
            $data = [];

            foreach ($result->lesenAntwort->titel[0]->einzelWerk as $item) {
                if (count($item->werte) > 1) {
                    $data[$item->feldName] = $item->werte;
                } else {
                    $data[$item->feldName] = $item->werte[0];
                }
            }

            return $data;

            // return $result->lesenAntwort->titel[0]->einzelWerk;
            // return array_map(function(array $data) {
            //     return $data->einzelWerk;
            // }, $result->lesenAntwort->titel);
        }

        return [];
    }


    /**
     * Fetches information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     * @return array
     */
    private function fetch(string $identifier, bool $forceRefresh = false): array
    {
        $value = null;

        if (!is_null($this->cache)) {
            # If specified ..
            if ($forceRefresh) {
                # .. clear cache beforehand
                $this->cache->delete($identifier);
            }

            # Retrieve from cache
            $value = $this->cache->get($identifier, function () use ($identifier) {
                return $this->_fetch($identifier);
            });
        }

        if (is_null($value)) {
            $value = $this->_fetch($identifier);
        }

        return $value;
    }


    /**
     * Instantiates 'Product' object from single EAN/ISBN
     *
     * @param string $identifier Product EAN/ISBN
     * @param bool $forceRefresh Whether to update cached data
     * @throws \Fundevogel\Pcbis\Exceptions\UnknownTypeException
     * @return \Fundevogel\Pcbis\Products\Product
     */
    public function load(string $identifier, bool $forceRefresh = false): Product
    {
        # Define available product types
        $types = [
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

        # Fetch raw data
        $data = $this->fetch($identifier, $forceRefresh);

        # Determine type identifier
        $code = $data['Sortimentskennzeichen'];

        # If product type is unknown ..
        if (!array_key_exists($code, $types)) {
            # .. fail early
            throw new UnknownTypeException(sprintf('Unknown type identifier: "%s"', $code));
        }

        # Create instance based on product type
        $type = $types[$code];

        switch ($type) {
            # Books
            case 'ePublikation':
                return new Ebook($data, $this->api);
            case 'Hardcover':
                return new Hardcover($data, $this->api);
            case 'Schulbuch':
                return new Schoolbook($data, $this->api);
            case 'Taschenbuch':
                return new Softcover($data, $this->api);

            # Media
            case 'Film':
                return new Movie($data, $this->api);
            case 'Hörbuch':
                return new Audiobook($data, $this->api);
            case 'Musik':
                return new Music($data, $this->api);
            case 'Tonträger':
                return new Sound($data, $this->api);

            # Nonbook
            case 'Games':
                return new Videogame($data, $this->api);
            case 'Kalender':
                return new Calendar($data, $this->api);
            case 'Landkarte/Globus':
                return new Map($data, $this->api);
            case 'Nonbook':
                return new Nonbook($data, $this->api);
            case 'Noten':
                return new Notes($data, $this->api);
            case 'Papeterie/PBS':
                return new Stationery($data, $this->api);
            case 'Software':
                return new Software($data, $this->api);
            case 'Spiel':
                return new Boardgame($data, $this->api);
            case 'Spielzeug':
                return new Toy($data, $this->api);
        }
    }
}
