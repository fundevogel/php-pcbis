<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api;

use Fundevogel\Pcbis\Traits\OlaStatus;

use stdClass;

/**
 * Class Ola
 *
 * Processes information about products being available for delivery - or not
 */
class Ola
{
    /**
     * Traits
     */

    use OlaStatus;


    /**
     * Properties
     */

    /**
     * Source OLA data as fetched from KNV's API
     *
     * @var \stdClass
     */
    public stdClass $data;


    /**
     * Constructor
     *
     * @param \stdClass $data OLA response ('Online-Lieferabfrage')
     */
    public function __construct(stdClass $data)
    {
        $this->data = $data->responseItems;
    }


    /**
     * Magic methods
     */

    /**
     * Exports availability when casting to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->isAvailable() ? 'verfügbar' : 'nicht verfügbar';
    }


    /**
     * Methods
     */

    /**
     * Checks whether OLA query succeeded
     *
     * Note: This differs from product availability!
     *
     * @return bool
     */
    public function hasSucceeded(): bool
    {
        return $this->data->status == 'OK';
    }


    /**
     * Checks whether OLA query failed
     *
     * Note: This differs from product availability!
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->data->status == 'FAILED';
    }


    /**
     * Checks whether KNV 'Meldenummer' is present
     *
     * @return bool
     */
    public function hasOlaCode(): bool
    {
        return $this->data->meldeNummer != '';
    }


    /**
     * Exports KNV 'Meldenummer' (if present)
     *
     * @return string
     */
    public function olaCode(): string
    {
        return $this->data->meldeNummer;
    }


    /**
     * Exports KNV 'Meldetext' (if present)
     *
     * @return string
     */
    public function olaMessage(): string
    {
        if (array_key_exists($this->olaCode(), $this->olaMessages)) {
            return $this->olaMessages[$this->olaCode()];
        }

        return $this->data->meldeText;
    }


    /**
     * Checks whether KNV 'Fehlernummer' is present
     *
     * @return bool
     */
    public function hasErrorCode(): bool
    {
        return $this->data->fehlerNummer != '';
    }


    /**
     * Exports KNV 'Fehlermmer' (if present)
     *
     * @return string
     */
    public function errorCode(): string
    {
        return $this->data->fehlerNummer;
    }


    /**
     * Exports KNV 'Fehlertext' (if present)
     *
     * @return string
     */
    public function errorMessage(): string
    {
        if (array_key_exists($this->errorCode(), $this->errorMessages)) {
            return $this->errorMessages[$this->errorCode()];
        }

        return $this->data->fehlerText;
    }


    /**
     * Checks if product is purchasable
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode(), $this->available);
        }

        return $this->hasSucceeded() && $this->data->numberOrdered <= $this->numberAvailable();
    }


    /**
     * Checks if product is permanently unpurchasable
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode(), $this->unavailable);
        }

        return !$this->isAvailable();
    }


    /**
     * Checks number of ordered items
     *
     * @return int
     */
    public function numberOrdered(): int
    {
        return $this->data->bestellMenge;
    }


    /**
     * Checks number of available items
     *
     * @return int
     */
    public function numberAvailable(): int
    {
        return $this->data->lieferbareMenge;
    }


    /**
     * TODO: Include remaining OLA codes:
     *
     * 22 Terminauftrag, vorgemerkt
     * 27 Vormerkung storniert
     * 59 Bestellung storniert
     * 63 Versandart Stornierung
     * 73 Fortsetzung
     */
}
