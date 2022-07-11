<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Types\Books;

use Fundevogel\Pcbis\Classes\Product\Types\Book;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
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
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function subtitle(): Value
    {
        if (!isset($this->data['Utitel'])) {
            return new Value();
        }

        if (Str::contains($this->data['Utitel'], 'Lesegerätegruppen')) {
            return new Value();
        }

        return new Value(A::first(Str::split($this->data['Utitel'], '.')));
    }


    /**
     * Dataset methods
     */

    /**
     * Exports supported devices
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function devices(): Value
    {
        if (!isset($this->data['Utitel'])) {
            return new Value();
        }

        $string = A::last(Str::split($this->data['Utitel'], 'Unterstützte Lesegerätegruppen:'));

        return new Value(array_map(function (string $string): string {
            if ($string == 'MAC') {
                return 'Mac';
            }

            if ($string == 'Tabl') {
                return 'Tablet';
            }

            return $string;
        }, Str::split($string, '/')));
    }


    /**
     * Exports print edition ISBN
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function printEdition(): Value
    {
        return new Value($this->data['PrintISBN'] ?? '');
    }


    /**
     * Exports file size (in megabytes)
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function fileSize(): Value
    {
        if (!isset($this->data['DateiGroesse'])) {
            return new Value();
        }

        $kilobytes = (int) Str::replace($this->data['DateiGroesse'], ' KB', '');

        return new Value(number_format($kilobytes / 1024, 2) . ' MB');
    }


    /**
     * Exports file format
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function fileFormat(): Value
    {
        if (!isset($this->data['DateiFormat'])) {
            return new Value();
        }

        # Be safe, trim strings
        return new Value(Str::lower(trim($this->data['DateiFormat'])));
    }


    /**
     * Exports DRM descriptor
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function drm(): Value
    {
        if (!isset($this->data['DRMFlags'])) {
            return new Value();
        }

        $flags = [
            '00' => 'kein DRM',
            '01' => 'Adobe DRM (benötigt Adobe Digital Editions)',
            '02' => 'Digitales Wasserzeichen',
            '03' => 'Adobe DRM (benötigt Adobe Digital Editions)',
        ];

        # Be safe, trim strings
        return new Value($flags[trim($this->data['DRMFlags'])]);
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
