<?php

namespace PHPCBIS\Products\Books\Types;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Books\Book;


/**
 * Class Ebook
 *
 * KNV product category 'ePublikation'
 *
 * @package PHPCBIS
 */

class Ebook extends Book {
    /**
     * Properties
     */

    /**
     * Supported devices
     *
     * @var array
     */
    protected $devices;


    /**
     * ISBN of print edition
     *
     * @var string
     */
    protected $print;


    /**
     * File size (in megabytes)
     *
     * @var string
     */
    protected $fileSize;


    /**
     * File format
     *
     * @var string
     */
    protected $fileFormat;


    /**
     * Digital Rights Management descriptor
     *
     * @var string
     */
    protected $drm;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        # Extend dataset
        $this->devices    = $this->buildDevices();
        $this->print      = $this->buildPrint();
        $this->fileSize   = $this->buildFileSize();
        $this->fileFormat = $this->buildFileFormat();
        $this->drm        = $this->buildDRM();
    }


    /**
     * Overrides
     */

    /**
     * Builds subtitle
     *
     * @return string
     */
    protected function buildSubtitle(): string
    {
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return '';
        }

        if (Butler::startsWith($this->source['Utitel'], 'Unterstützte Lesegerätegruppen')) {
            return '';
        }

        return Butler::first(Butler::split($this->source['Utitel'], '.'));
    }


    /**
     * Exports all data
     *
     * @return array
     */
    public function export(bool $asArray = false): array {
        # Build dataset
        return array_merge(
            # (1) 'Book' dataset
            parent::export($asArray), [
            # (2) 'Ebook' specific data
            'Unterstützt'  => $this->devices(),
            'Printausgabe' => $this->print(),
            'Dateigröße'   => $this->fileSize(),
            'Dateiformat'  => $this->fileFormat(),
            'DRM'          => $this->drm(),
        ]);
    }


    /**
     * Methods
     */

    protected function buildDevices() {
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return '';
        }

        $data = Butler::last(Butler::split($this->source['Utitel'], 'Unterstützte Lesegerätegruppen:'));

        return Butler::split($data, '/');
    }


    /**
     * Returns supported devices
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return string|array
     */
    public function devices(bool $asArray = false)
    {
        if ($isArray) {
            return $this->devices;
        }

        return Butler::join($this->devices, ' / ');
    }


    /**
     * Builds ISBN of print edition
     *
     * @return string
     */
    protected function buildPrint(): string
    {
        if (!isset($this->source['PrintISBN'])) {
            return '';
        }

        return $this->source['PrintISBN'];
    }


    /**
     * Returns ISBN of print edition
     *
     * @return string
     */
    public function print(): string
    {
        return $this->print;
    }


    /**
     * Builds file size (in megabytes)
     *
     * @return string
     */
    protected function buildFileSize(): string
    {
        if (!isset($this->source['DateiGroesse'])) {
            return '';
        }

        $kilobytes = (int) Butler::replace($this->source['DateiGroesse'], ' KB', '');

        return number_format($kilobytes / 1024, 2) . ' MB';
    }


    /**
     * Returns file size
     *
     * @return string
     */
    public function fileSize(): string
    {
        return $this->fileSize;
    }


    /**
     * Builds file format
     *
     * @return string
     */
    protected function buildFileFormat(): string
    {
        if (!isset($this->source['DateiFormat'])) {
            return '';
        }

        return Butler::lower($this->source['DateiFormat']);
    }


    /**
     * Returns file format
     *
     * @return string
     */
    public function fileFormat(): string
    {
        return $this->fileFormat;
    }


    /**
     * Builds DRM descriptor
     *
     * @return string
     */
    protected function buildDRM(): string
    {
        if (!isset($this->source['DRMFlags'])) {
            return '';
        }

        $flags = [
            '00' => 'kein DRM',
            '01' => 'Adobe DRM (benötigt Adobe Digital Editions)',
            '02' => 'Digitales Wasserzeichen',
            '03' => 'Adobe DRM (benötigt Adobe Digital Editions)',
        ];

        return $flags[$this->source['DRMFlags']];
    }


    /**
     * Returns DRM descriptor
     *
     * @return string
     */
    public function drm(): string
    {
        return $this->drm;
    }
}
