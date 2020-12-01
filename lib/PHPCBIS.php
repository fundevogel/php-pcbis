<?php

/**
 * PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/pcbis2pdf
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace PHPCBIS;

use PHPCBIS\Exceptions\InvalidISBNException;
use PHPCBIS\Helpers\Butler;
use PHPCBIS\KNV\Api;

use PHPCBIS\Products\Factory;
use PHPCBIS\Products\Books\Books;

use Biblys\Isbn\Isbn as ISBN;
use Doctrine\Common\Cache\FilesystemCache as FileCache;


/**
 * Class PHPCBIS
 *
 * Retrieves information from KNV's API & caches the resulting data
 *
 * @package PHPCBIS
 */

class PHPCBIS
{
    /**
     * Current version number of PHPCBIS
     */
    const VERSION = '2.0.0-rc.3';


    /**
     * Object granting access to KNV's API
     *
     * @var \PHPCBIS\KNV\Api
     */
    private $api = null;


    /**
     * Cache object storing product data fetched from KNV's API
     *
     * @var \Doctrine\Common\Cache\FilesystemCache
     */
    private $cache = null;


    /**
     * Path to cached product data fetched from KNV's API
     *
     * @var string
     */
    private $cachePath = './.cache';


    /**
     * Whether cached data should be refreshed
     *
     * @var bool
     */
    private $forceRefresh;


    /**
     * Translatable strings
     *
     * @var array
     */
    private $translations = [];


    /**
     * Constructor
     */

    public function __construct(array $credentials = null, bool $forceRefresh = false)
    {
        # Connect with API
        $this->api = new Api($credentials);

        # Initialise cache
        $this->cache = new FileCache($this->cachePath);

        # Force cache refresh (or not)
        $this->forceRefresh = $forceRefresh;
    }


    /**
     * Setters & getters
     */

    public function setCachePath(string $cachePath)
    {
        # Reinitialise cache object
        $this->cache = new FileCache($cachePath);

        # Set path to product data
        $this->cachePath = $cachePath;
    }


    public function getCachePath()
    {
        return $this->cachePath;
    }


    public function setForceRefresh(bool $forceRefresh)
    {
        $this->forceRefresh = $forceRefresh;
    }


    public function getForceRefresh(): bool
    {
        return $this->forceRefresh;
    }


    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }


    public function getTranslations(): array
    {
        return $this->translations;
    }


    /**
     * Methods
     */

    /**
     * Fetches information from cache if they exist,
     * otherwise loads them & saves to cache
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @return array
     */
    private function fetch(string $isbn): array
    {
        if ($this->cache->contains($isbn) && $this->forceRefresh) {
            $this->cache->delete($isbn);
        }

        # Data might be cached already ..
        $fromCache = true;

        if (!$this->cache->contains($isbn)) {
            $result = $this->api->query($isbn);
            $this->cache->save($isbn, $result);

            # .. turns out, it was not
            $fromCache = false;
        }

        return [
            'fromCache' => $fromCache,
            'source'    => $this->cache->fetch($isbn),
        ];
    }


    /**
     * Validates and formats given EAN/ISBN
     * For more information, see https://github.com/biblys/isbn
     *
     * @param string $isbn - International Standard Book Number
     * @throws \PHPCBIS\Exceptions\InvalidISBNException
     * @return string
     */
    private function validate(string $isbn): string
    {
        if (Butler::length($isbn) === 13 && (Butler::startsWith($isbn, '4') || Butler::startsWith($isbn, '5'))) {
            # Most likely non-convertable EAN
            return $isbn;
        }

        $isbn = new ISBN($isbn);

        try {
            $isbn->validate();
            $isbn = $isbn->format('ISBN-13');
        } catch(\Exception $e) {
            throw new InvalidISBNException($e->getMessage());
        }

        return $isbn;
    }


    /**
     * Checks if product is available for delivery via OLA query
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @param int $quantity - Number of products to be delivered
     * @return \PHPCBIS\KNV\Responses\OLA
     */
    public function ola(string $isbn, int $quantity = 1)
    {
        $isbn = $this->validate($isbn);

        return $this->api->ola($isbn, $quantity);
    }


    /**
     * Instantiates `Product` object from single EAN/ISBN
     *
     * @param string $isbn - A given product's EAN/ISBN
     * @return \PHPCBIS\Products\Product
     */
    public function load(string $isbn): \PHPCBIS\Products\Product
    {
        $isbn = $this->validate($isbn);
        $data = $this->fetch($isbn);

        $props = [
            'isbn'         => $isbn,
            'fromCache'    => $data['fromCache'],
            'translations' => $this->translations,
        ];

        return Factory::factory($data['source'], $props);
    }


    /**
     * Instantiates `Books` object from multiple EANs/ISBNs
     *
     * TODO: This needs to be re-evaluated / outsourced to a factory
     *
     * @param array $isbns - A group of books' ISBNs
     * @return \PHPCBIS\Products\Books\Books
     */
    public function loadBooks(array $isbns): \PHPCBIS\Products\Books\Books
    {
        $books = [];

        foreach ($isbns as $isbn) {
            try {
                $book = $this->load($isbn);

                if ($book->isBook() || $book->isAudiobook()) {
                    $books[] = $book;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return new Books($books);
    }
}
