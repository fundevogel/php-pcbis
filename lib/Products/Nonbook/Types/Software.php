<?php

namespace Pcbis\Products\Nonbook\Types;

use Pcbis\Helpers\Butler;
use Pcbis\Products\Nonbook\Item;


/**
 * Class Software
 *
 * KNV product category 'Software'
 *
 * @package PHPCBIS
 */

class Software extends Item {
    /**
     * Properties
     */

    /**
     * Version schema
     *
     * @var array
     */
    protected $version;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props)
    {
        parent::__construct($source, $props);

        # Extend dataset
        $this->version = $this->buildVersion();
    }


    /**
     * Methods
     */

    /**
     * Builds version schema
     *
     * @return string
     */
    protected function buildVersion(): string
    {
        if (!isset($this->source['Abb'])) {
            return '';
        }

        $version = '';

        # TODO: Improve regex for schema like 1.23.10
        # .. is that even a thing?
        if (preg_match('/Version\s\d{0,2}(?:[.,]\d{1,2})?/', $this->source['Abb'], $matches)) {
            $version = $matches[0];
        }

        # Check title for version if first approach fails
        if (empty($version) && isset($this->source['Titel'])) {
            $string = $this->source['Titel'];

            # Remove strings indicating number of CDs/DVDs involved
            if (preg_match('/\d{1,2}\s[A-Z]{2,3}-ROMs?/', $this->source['Titel'], $matches)) {
                $string = Butler::replace($string, $matches[0], '');
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
     * Exports version schema
     *
     * @return string
     */
    public function version(): string
    {
        return $this->version;
    }


    /**
     * Whether software is educational
     *
     * @return bool
     */
    public function isEducational(): bool
    {
        if (isset($this->source['SonstTxt'])) {
            return Butler::contains(Butler::lower($this->source['SonstTxt']), '14 juschg');
        }

        return false;
    }


    /**
     * Overrides
     */

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
            # (1) 'Item' dataset
            parent::export($asArray), [
            # (2) 'Software' specific data
            'Version' => $this->version(),
        ]);
    }
}
