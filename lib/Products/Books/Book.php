<?php

namespace Pcbis\Products\Books;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Product;


/**
 * Class Book
 *
 * @package PHPCBIS
 */

class Book extends Product
{
    /**
     * Properties
     */

    /**
     * Binding
     *
     * @var string
     */
    protected $binding;


    /**
     * Page count
     *
     * @var string
     */
    protected $pageCount;


    /**
     * Antolin rating (suitable grade)
     *
     * @var string
     */
    protected $antolin = '';


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->publisher    = $this->buildPublisher();
        $this->binding      = $this->buildBinding();
        $this->pageCount    = $this->buildPageCount();
        $this->antolin      = $this->buildAntolin();
    }


    /**
     * Methods
     */

    /**
     * Builds binding
     *
     * @return string
     */
    protected function buildBinding(): string
    {
        if (!isset($this->source['Einband'])) {
            return '';
        }

        $bindings = json_decode(file_get_contents(__DIR__ . '/../../../resources/binding_codes.json'), true);

        if (!isset($bindings[$this->source['Einband']])) {
            return $this->source['Einband'];
        }

        return $bindings[$this->source['Einband']];
    }


    /**
     * Exports binding
     *
     * @return string
     */
    public function binding(): string
    {
        return $this->binding;
    }


    /**
     * Builds page count
     *
     * @return string
     */
    protected function buildPageCount(): string
    {
        if (!isset($this->source['Abb'])) {
            return '';
        }

        $lines = Butler::split($this->source['Abb'], '.');

        foreach ($lines as $line) {
            if (Butler::substr($line, -1) === 'S') {
                return Butler::split($line, ' ')[0];
            }
        }

        return '';
    }


    /**
     * Exports page count
     *
     * @return string
     */
    public function pageCount(): string
    {
        return $this->pageCount;
    }


    /**
     * Builds Antolin rating
     *
     * @return array|string
     */
    protected function buildAntolin()
    {
        if (empty($this->tags)) {
            return '';
        }

        foreach ($this->tags as $tag) {
            if (Butler::startsWith($tag, 'Antolin')) {
                return Butler::replace($tag, ['Antolin (', ')'], '');
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
        return $this->antolin;
    }


    /**
     * Exports all data
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        return array_merge(
            # Build dataset
            parent::export($asArray), [
            # (1) 'Book' specific data
            'Einband'       => $this->binding(),
            'Seitenzahl'    => $this->pageCount(),
            'Antolin'       => $this->antolin(),

            # (2) Extension 'People'
            'AutorIn'       => $this->getRole('author', $asArray),
            'Vorlage'       => $this->getRole('original', $asArray),
            'IllustratorIn' => $this->getRole('illustrator', $asArray),
            'ZeichnerIn'    => $this->getRole('drawer', $asArray),
            'PhotographIn'  => $this->getRole('photographer', $asArray),
            'ÜbersetzerIn'  => $this->getRole('translator', $asArray),
            'HerausgeberIn' => $this->getRole('editor', $asArray),
            'MitarbeiterIn' => $this->getRole('participant', $asArray),
        ]);
    }
}
