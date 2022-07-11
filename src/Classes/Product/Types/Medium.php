<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Classes\Product\Types;

use Fundevogel\Pcbis\Classes\Product\Product;
use Fundevogel\Pcbis\Classes\Product\Fields\Value;
use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Str;

/**
 * Class Medium
 *
 * Base class for media
 */
class Medium extends Product
{
    /**
     * Dataset methods
     */

    /**
     * Exports duration
     *
     * @return \Fundevogel\Pcbis\Classes\Product\Fields\Value
     */
    public function duration(): Value
    {
        # TODO: Prevent subtitle containing duration
        if (!isset($this->data['Utitel'])) {
            return new Value();
        }

        $array = Str::split($this->data['Utitel'], '.');

        return new Value(Str::replace(A::last($array), ' Min', ''));
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
            # (1) 'Media' specific data
            'Dauer'         => $this->duration()->value(),
            'KomponistIn'   => $this->getRole('composer')->value(),
            'RegisseurIn'   => $this->getRole('director')->value(),
            'ProduzentIn'   => $this->getRole('producer')->value(),

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
