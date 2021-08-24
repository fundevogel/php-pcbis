<?php

namespace Pcbis\Products\Books\Types;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Books\Book;


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

    public function __construct(array $source, array $props)
    {
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
     * Methods
     */

    /**
     * Builds supported devices
     *
     * @return array
     */
    protected function buildDevices(): array
    {
        if (!isset($this->source['Utitel']) || $this->source['Utitel'] == null) {
            return [];
        }

        $string = Butler::last(Butler::split($this->source['Utitel'], 'Unterstützte Lesegerätegruppen:'));

        return Butler::split(Butler::replace($string, ['MAC', 'Tabl'], ['Mac', 'Tablet']), '/');
    }


    /**
     * Exports supported devices
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return string|array
     */
    public function devices(bool $asArray = false)
    {
        if ($asArray) {
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
     * Exports ISBN of print edition
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
     * Exports file size
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
     * Exports file format
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

        # Be safe, trim strings
        return $flags[trim($this->source['DRMFlags'])];
    }


    /**
     * Exports DRM descriptor
     *
     * @return string
     */
    public function drm(): string
    {
        return $this->drm;
    }


    /**
     * Exports all data
     *
     * @param bool $asArray - Whether to export an array (rather than a string)
     * @return array
     */
    public function export(bool $asArray = false): array
    {
        # Build dataset
        return array_merge(
            # (1) 'Book' dataset
            parent::export($asArray), [
            # (2) 'Ebook' specific data
            'Lesegeräte'   => $this->devices(),
            'Printausgabe' => $this->print(),
            'Dateigröße'   => $this->fileSize(),
            'Dateiformat'  => $this->fileFormat(),
            'DRM'          => $this->drm(),
        ]);
    }
}
