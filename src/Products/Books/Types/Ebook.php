<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products\Books\Types;

use Fundevogel\Pcbis\Products\Books\Book;

use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;

/**
 * Class Ebook
 *
 * KNV product category 'ePublikation'
 */
class Ebook extends Book
{
    /**
     * Overrides
     */

    /**
     * Exports subtitle
     *
     * @return string
     */
    public function subtitle(): string
    {
        if (!isset($this->source['Utitel'])) {
            return '';
        }

        if (Str::contains($this->source['Utitel'], 'Lesegerätegruppen')) {
            return '';
        }

        return A::first(Str::split($this->source['Utitel'], '.'));
    }


    /**
     * Dataset methods
     */

    /**
     * Exports supported devices
     *
     * @return array
     */
    public function devices(): array
    {
        if (!isset($this->source['Utitel'])) {
            return [];
        }

        $string = A::last(Str::split($this->source['Utitel'], 'Unterstützte Lesegerätegruppen:'));

        return Str::split(Str::replace($string, ['MAC', 'Tabl'], ['Mac', 'Tablet']), '/');
    }


    /**
     * Exports print edition ISBN
     *
     * @return string
     */
    public function printEdition(): string
    {
        if (!isset($this->source['PrintISBN'])) {
            return '';
        }

        return $this->source['PrintISBN'];
    }


    /**
     * Exports file size (in megabytes)
     *
     * @return string
     */
    public function fileSize(): string
    {
        if (!isset($this->source['DateiGroesse'])) {
            return '';
        }

        $kilobytes = (int) Str::replace($this->source['DateiGroesse'], ' KB', '');

        return number_format($kilobytes / 1024, 2) . ' MB';
    }


    /**
     * Exports file format
     *
     * @return string
     */
    public function fileFormat(): string
    {
        if (!isset($this->source['DateiFormat'])) {
            return '';
        }

        # Be safe, trim strings
        return Str::lower(trim($this->source['DateiFormat']));
    }


    /**
     * Exports DRM descriptor
     *
     * @return string
     */
    public function drm(): string
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
     * Exports all data
     *
     * @return array
     */
    public function export(): array
    {
        # Build dataset
        return array_merge(parent::export(), [
            # 'Ebook' specific data
            'Lesegeräte'   => $this->devices(),
            'Printausgabe' => $this->printEdition(),
            'Dateigröße'   => $this->fileSize(),
            'Dateiformat'  => $this->fileFormat(),
            'DRM'          => $this->drm(),
        ]);
    }
}
