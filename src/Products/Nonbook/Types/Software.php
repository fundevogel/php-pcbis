<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Products\Nonbook\Types;

use Fundevogel\Pcbis\Helpers\Str;
use Fundevogel\Pcbis\Products\Nonbook\Item;

/**
 * Class Software
 *
 * KNV product category 'Software'
 */
class Software extends Item
{
    /**
     * Dataset methods
     */

    /**
     * Exports version schema
     *
     * @return string
     */
    public function version(): string
    {
        if (!isset($this->data['Abb'])) {
            return '';
        }

        $version = '';

        # TODO: Improve regex for schema like 1.23.10
        # .. is that even a thing?
        if (preg_match('/Version\s\d{0,2}(?:[.,]\d{1,2})?/', $this->data['Abb'], $matches)) {
            $version = $matches[0];
        }

        # Check title for version if first approach fails
        if (empty($version) && isset($this->data['Titel'])) {
            $string = $this->data['Titel'];

            # Remove strings indicating number of CDs/DVDs involved
            if (preg_match('/\d{1,2}\s[A-Z]{2,3}-ROMs?/', $this->data['Titel'], $matches)) {
                $string = Str::replace($string, $matches[0], '');
            }

            # Look for simple number to use as version ..
            if (preg_match_all('/\d{1,2}/', $string, $matches)) {
                # .. but only if there's one match, otherwise '2 in 1' becomes 'v2'
                if (count($matches[0]) === 1) {
                    $version = $matches[0][0];
                }
            }
        }

        return $version;
    }


    /**
     * Checks whether software is educational
     *
     * @return bool
     */
    public function isEducational(): bool
    {
        if (isset($this->data['SonstTxt'])) {
            return Str::contains(Str::lower($this->data['SonstTxt']), '14 juschg');
        }

        return false;
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
            # 'Software' specific data
            'Version' => $this->version(),
        ]);
    }
}
