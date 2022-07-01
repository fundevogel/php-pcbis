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
     * Whether current OLA query was successful
     *
     * @var bool
     */
    private $success;


    /**
     * Quantity being checked
     *
     * @var int
     */
    private $quantityOrdered;


    /**
     * Quantity in stock
     *
     * @var int
     */
    private $quantityAvailable;


    /**
     * Current KNV 'Fehlernummer'
     *
     * @var string
     */
    private $errorCode = null;


    /**
     * Current KNV 'Fehlernummer' description
     *
     * @var string
     */
    private $errorMessage = null;


    /**
     * All KNV 'Fehlernummer' descriptions
     *
     * @var array
     */
    private $errorMessages = [
        '19003' => 'Benutzerfehler',
        '19004' => 'Passwortfehler',
        '19005' => 'Hostfehler',
        '19006' => 'Falsche ACT',
        '19007' => 'Verkehrsnummer fehlt',
        '19008' => 'Bestellnummer fehlt',
        '19009' => 'Menge fehlt',
        '19010' => 'Kommunikationsfehler',
        '19011' => 'Antwortfehler',
        '19012' => 'Antwortunterbrechung',
        '19013' => 'Timeout',
        '19014' => 'Busy',
        '19015' => 'No carrier',
        '19016' => 'Beeendigungsfehler',
        '19017' => 'Schreibfehler',
        '19018' => 'OLA-Konfiguration fehlt',
        '19031' => 'Bei einer OLA-Anfrage darf die Menge maximal 99 betragen',
        '19032' => 'Fehlende Referenznummer',
        '19033' => 'Fehlendes Bestelldatum',
        '19034' => 'Menge darf bei einer Onlinebestellung maximal 30000 betragen',
        '19040' => 'Fehler bei der TCPIP Initialisierung',
        '19041' => 'Fehler beim TCPIP Connect',
        '19050' => 'Referenznummer konnte nicht generiert werden',
        '19060' => 'Keine Vormerkung gefunden',
        '19061' => 'Storno nicht erlaubt',
        # TODO: 19062 ?
    ];


    /**
     * Constructor
     *
     * @param \stdClass $data OLA response ('Online-Lieferabfrage')
     */
    public function __construct(public stdClass $data)
    {
        $this->data = $data->OLAResponse->OLAResponseRecord;

        # Whether OLA query for given product was successfull
        # Note: This doesn't convern given product's availability!
        $this->success = $this->data->StatusPosition === 'OK' ? true : false;

        # Number of items ordered & available for order
        $this->quantityOrdered = $this->data->Bestellmenge;
        $this->quantityAvailable = $this->data->Lieferbaremenge;

        # Set OLA code & message
        if (isset($this->data->Meldenummer)) {
            $this->olaCode = (string) $this->data->Meldenummer;
        }

        if (array_key_exists($this->olaCode, $this->olaMessages)) {
            $this->olaMessage = $this->olaMessages[$this->olaCode];
        }

        # Set error code & error message
        if (isset($this->data->Fehlercode)) {
            $this->errorCode = (string) $this->data->Fehlercode;
        }

        if (array_key_exists($this->errorCode, $this->errorMessages)) {
            $this->errorMessage = $this->errorMessages[$this->errorCode];
        } elseif (isset($this->data->Fehlertext)) {
            $this->errorMessage = $this->data->Fehlertext;
        }
    }


    /**
     * Magic methods
     */

    /**
     * Print availability when casting to string
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
     * Checks if KNV 'Fehlernummer' is available
     *
     * @return bool
     */
    public function hasErrorCode(): bool
    {
        return $this->errorCode !== null;
    }


    /**
     * Prints current KNV 'Fehlernummer'
     *
     * @return string
     */
    public function errorCode(): string
    {
        if ($this->hasErrorCode()) {
            return $this->errorCode;
        }

        return '';
    }


    /**
     * Checks if KNV 'Fehlertext' is available
     *
     * @return bool
     */
    public function hasErrorMessage(): bool
    {
        return $this->errorMessage !== null;
    }


    /**
     * Prints current KNV 'Fehlertext'
     *
     * @return string
     */
    public function errorMessage(): string
    {
        if ($this->hasErrorMessage()) {
            return $this->errorMessage;
        }

        return '';
    }


    /**
     * Checks if product is available / may be purchased
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->available);
        }

        return $this->success && $this->quantityOrdered <= $this->quantityAvailable;
    }


    /**
     * Checks if product is permanently unavailable
     *
     * @return bool
     */
    public function isUnavailable(): bool
    {
        if ($this->hasOlaCode()) {
            return in_array($this->olaCode, $this->unavailable);
        }

        return !$this->isAvailable();
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
