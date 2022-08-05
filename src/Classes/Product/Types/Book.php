<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Types;

use Fundevogel\Pcbis\Api\Webservice;
use Fundevogel\Pcbis\Classes\Product\Product;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
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
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function binding(): Value
    {
        if (!isset($this->data['Einband'])) {
            return new Value();
        }

        # Be safe, trim strings
        $binding = trim($this->data['Einband']);

        if (!array_key_exists($binding, $this->bindings)) {
            return new Value($binding);
        }

        return new Value($this->bindings[$binding]);
    }


    /**
     * Exports page count
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function pageCount(): Value
    {
        if (!isset($this->data['Abb'])) {
            return new Value();
        }

        $lines = Str::split($this->data['Abb'], '.');

        foreach ($lines as $line) {
            if (Str::substr($line, -1) === 'S') {
                return new Value(Str::split($line, ' ')[0]);
            }
        }

        return new Value();
    }


    /**
     * Exports Antolin rating
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function antolin(): Value
    {
        foreach ($this->getTags() as $tag) {
            if (Str::startsWith($tag, 'Antolin')) {
                return new Value(Str::replace($tag, ['Antolin (', ')'], ''));
            }
        }

        return new Value();
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
            'Einband'       => $this->binding()->value(),
            'Seitenzahl'    => $this->pageCount()->value(),
            'Antolin'       => $this->antolin()->value(),

            # (2) Extension 'People'
            'AutorIn'       => $this->getRole('author')->value(),
            'Vorlage'       => $this->getRole('original')->value(),
            'IllustratorIn' => $this->getRole('illustrator')->value(),
            'ZeichnerIn'    => $this->getRole('drawer')->value(),
            'PhotographIn'  => $this->getRole('photographer')->value(),
            'ÜbersetzerIn'  => $this->getRole('translator')->value(),
            'HerausgeberIn' => $this->getRole('editor')->value(),
            'MitarbeiterIn' => $this->getRole('participant')->value(),
        ]);
    }
}
