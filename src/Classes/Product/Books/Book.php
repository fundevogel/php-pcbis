<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Books;

use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Classes\Product\Product;
use Fundevogel\Pcbis\Helpers\Str;

/**
 * Class Book
 *
 * Base class for books
 */
class Book extends Product
{
    /**
     * Properties
     */

    /**
     * Binding codes
     *
     * @var array
     */
    protected $bindings;


    /**
     * Constructor
     *
     * @param array $data Source data fetched from KNV's API
     * @param \Fundevogel\Pcbis\Api\Webservice $api Object granting access to KNV's API
     */
    public function __construct(array $data, ?Webservice $api = null)
    {
        # Execute default constructor
        parent::__construct($data, $api);

        # Load binding codes
        $this->bindings = json_decode(file_get_contents(__DIR__ . '/../../../../data/codes.json'), true);
    }


    /**
     * Dataset methods
     */

    /**
     * Exports binding
     *
     * @return string
     */
    public function binding(): string
    {
        if (!isset($this->data['Einband'])) {
            return '';
        }

        # Be safe, trim strings
        $binding = trim($this->data['Einband']);

        if (!array_key_exists($binding, $this->bindings)) {
            return $binding;
        }

        return $this->bindings[$binding];
    }


    /**
     * Exports page count
     *
     * @return string
     */
    public function pageCount(): string
    {
        if (!isset($this->data['Abb'])) {
            return '';
        }

        $lines = Str::split($this->data['Abb'], '.');

        foreach ($lines as $line) {
            if (Str::substr($line, -1) === 'S') {
                return Str::split($line, ' ')[0];
            }
        }

        return '';
    }


    /**
     * Exports Antolin rating
     *
     * @return string
     */
    public function antolin(): string
    {
        if (empty($this->tags)) {
            return '';
        }

        foreach ($this->tags as $tag) {
            if (Str::startsWith($tag, 'Antolin')) {
                return Str::replace($tag, ['Antolin (', ')'], '');
            }
        }

        return '';
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # (1) 'Book' specific data
            'ISBN'          => $this->isbn(),
            'Einband'       => $this->binding(),
            'Seitenzahl'    => $this->pageCount(),
            'Antolin'       => $this->antolin(),

            # (2) Extension 'People'
            'AutorIn'       => $this->getRole('author'),
            'Vorlage'       => $this->getRole('original'),
            'IllustratorIn' => $this->getRole('illustrator'),
            'ZeichnerIn'    => $this->getRole('drawer'),
            'PhotographIn'  => $this->getRole('photographer'),
            'ÜbersetzerIn'  => $this->getRole('translator'),
            'HerausgeberIn' => $this->getRole('editor'),
            'MitarbeiterIn' => $this->getRole('participant'),
        ]);
    }
}
