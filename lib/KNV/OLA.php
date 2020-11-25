<?php

namespace PHPCBIS\KNV;

use PHPCBIS\Helpers\Butler;


/**
 * Class OLA
 *
 * Processes information about books being available for delivery - or not
 *
 * @package PHPCBIS
 */

class OLA
{
    /**
     * OLAResponseRecord (as returned by an OLA call)
     *
     * @var \stdClass
     */
    private $data;


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
     * Status codes of available books
     *
     * Preorder always:
     *
     * 18 Wird besorgt – nicht remittierbar/nicht stornierbar
     * 97 Print on Demand (ggf. mit Angabe der Lieferzeit) – nicht remittierbar/nicht stornierbar
     *
     * Preorder possible:
     * 11 Erscheint laut Verlag/Lieferant .../... in neuer Auflage/als Nachfolgeprodukt
     * 12 Nachdruck/wird nachproduziert. Folgt laut Verlag/Lieferant .../...
     * 15 Fehlt kurzfristig am Lager
     * 21 Noch nicht erschienen. Erscheint laut Verlag/Lieferant ...
     * 23 Titel wegen Lieferverzug des Verlags/der Verlagsauslieferung derzeit nicht lieferbar
     * 25 Artikel neu aufgenommen. Noch nicht am Lager
     * 80 Fehlt, da der Verlag/Lieferant derzeit nicht liefern kann
     * 98 Folgt mit nächster Lieferung
     *
     * @var array
     */
    private $available = [
        '11',
        '12',
        '15',
        '18',
        '21',
        '23',
        '25',
        '80',
        '97',
        '98',
    ];


    /**
     * Status codes of unavailable books
     *
     * 07 Vergriffen, keine Neuauflage, Bestellung abgelegt
     * 17 Führen wir nicht bzw. nicht mehr
     * 19 Ladenpreis aufgehoben. Führen wir nicht mehr
     * 20 Noch nicht erschienen. Bestellung nicht vorgemerkt
     * 24 Erscheint nicht laut Verlag/Lieferant
     * 28 Titelnummer unbekannt
     * 29 ISBN oder EAN unbekannt
     * 43 Vergriffen – Neuauflage/Nachfolgeprodukt unbestimmt – Bestellung wird nicht vorgemerkt
     * 60 Indiziert. Führen wir nicht mehr
     * 62 Artikel infolge rechtlicher Auseinandersetzungen zur Zeit nicht lieferbar. Bestellung nicht vorgemerkt
     * 88 Konditionsänderung durch den Verlag/Lieferanten. Führen wir nicht mehr
     * 94 Wird zur Zeit nur ab Verlag/Lieferant geliefert – Bestellung nicht vorgemerkt
     * 99 Titel hat Nachfolgetitel/-auflage
     *
     * @var array
     */
    private $unavailable = [
         '7',
        '17',
        '19',
        '20',
        '24',
        '28',
        '29',
        '43',
        '60',
        '62',
        '88',
        '94',
        '99',
    ];


    /**
     * Current KNV 'Meldenummer'
     *
     * @var string
     */
    private $statusCode = null;


    /**
     * Current KNV 'Meldenummer' description
     *
     * @var string
     */
    private $statusMessage = null;


    /**
     * All KNV 'Meldenummer' descriptions
     *
     * @var array
     */
    private $statusMessages = [
         '7' => 'Vergriffen, keine Neuauflage, Bestellung abgelegt',
        '11' => 'Erscheint laut Verlag/Lieferant .../... in neuer Auflage/als Nachfolgeprodukt',
        '12' => 'Nachdruck/wird nachproduziert. Folgt laut Verlag/Lieferant .../...',
        '15' => 'Fehlt kurzfristig am Lager',
        '17' => 'Führen wir nicht bzw. nicht mehr',
        '18' => 'Wird besorgt – nicht remittierbar/nicht stornierbar',
        '19' => 'Ladenpreis aufgehoben. Führen wir nicht mehr',
        '20' => 'Noch nicht erschienen. Bestellung nicht vorgemerkt',
        '21' => 'Noch nicht erschienen. Erscheint laut Verlag/Lieferant ...',
        '22' => 'Terminauftrag, vorgemerkt',
        '24' => 'Erscheint nicht laut Verlag/Lieferant',
        '23' => 'Titel wegen Lieferverzug des Verlags/der Verlagsauslieferung derzeit nicht lieferbar',
        '25' => 'Artikel neu aufgenommen. Noch nicht am Lager',
        '27' => 'Vormerkung storniert',
        '28' => 'Titelnummer unbekannt',
        '29' => 'ISBN oder EAN unbekannt',
        '43' => 'Vergriffen – Neuauflage/Nachfolgeprodukt unbestimmt – Bestellung wird nicht vorgemerkt',
        '59' => 'Bestellung storniert',
        '60' => 'Indiziert. Führen wir nicht mehr',
        '62' => 'Artikel infolge rechtlicher Auseinandersetzungen zur Zeit nicht lieferbar. Bestellung nicht vorgemerkt',
        '63' => 'Versandart Stornierung',
        '73' => 'Fortsetzung',
        '80' => 'Fehlt, da der Verlag/Lieferant derzeit nicht liefern kann',
        '88' => 'Konditionsänderung durch den Verlag/Lieferanten. Führen wir nicht mehr',
        '94' => 'Wird zur Zeit nur ab Verlag/Lieferant geliefert – Bestellung nicht vorgemerkt',
        '97' => 'Print on Demand (ggf. mit Angabe der Lieferzeit) – nicht remittierbar/nicht stornierbar',
        '98' => 'Folgt mit nächster Lieferung',
        '99' => 'Titel hat Nachfolgetitel/-auflage',
    ];


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
     */

    public function __construct(\stdClass $data)
    {
        $this->data = $data;

        # Whether OLA query for given book was successfull
        # Note: This doesn't convern given book's availability!
        $this->success = $data->StatusPosition === 'OK' ? true : false;

        # Number of items ordered & available for order
        $this->quantityOrdered = $data->Bestellmenge;
        $this->quantityAvailable = $data->Lieferbaremenge;

        # Set status code & status message
        if (isset($data->Meldenummer)) {
            $this->statusCode = (string) $data->Meldenummer;
        }

        if (array_key_exists($this->statusCode, $this->statusMessages)) {
            $this->statusMessage = $this->statusMessages[$this->statusCode];
        }

        # Set error code & error message
        if (isset($data->Fehlercode)) {
            $this->errorCode = (string) $data->Fehlercode;
        }

        if (array_key_exists($this->errorCode, $this->errorMessages)) {
            $this->errorMessage = $this->errorMessages[$this->errorCode];
        } elseif (isset($data->Fehlertext)) {
            $this->errorMessage = $data->Fehlertext;
        }
    }


    /**
     * Magic methods
     */

    public function __toString(): string
    {
        return $this->success ? 'Verfügbar' : 'Nicht verfügbar';
    }


    /**
     * Methods
     */

    /**
     * Shows original OLA query fetched from KNV's API
     *
     * @return \stdObject
     */
    public function showSource(): \stdObject
    {
        return $this->data;
    }


    /**
     * Checks if KNV 'Meldenummer' is available
     *
     * @return bool
     */
    public function hasStatusCode(): bool
    {
        return $this->statusCode !== null;
    }


    /**
     * Prints current KNV 'Meldenummer'
     *
     * @return string
     */
    public function statusCode(): string
    {
        if (hasStatusCode()) {
            return $this->statusCode;
        }

        return '';
    }


    /**
     * Checks if KNV 'Meldenummer' description is available
     *
     * @return bool
     */
    public function hasStatusMessage(): bool
    {
        return $this->statusMessage !== null;
    }


    /**
     * Prints current KNV 'Meldenummer' description
     *
     * @return string
     */
    public function statusMessage(): string
    {
        if (hasStatusMessage()) {
            return $this->statusMessage;
        }

        return '';
    }


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
        if (hasErrorCode()) {
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
     * Checks if book is available / may be purchased
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->hasStatusCode()) {
            return array_key_exists($this->statusCode, $this->available);
        }

        return $this->success && $this->quantityOrdered <= $this->quantityAvailable;
    }


    /**
     * TODO: Include remaining status codes:
     *
     * 22 Terminauftrag, vorgemerkt
     * 27 Vormerkung storniert
     * 59 Bestellung storniert
     * 63 Versandart Stornierung
     * 73 Fortsetzung
     */
}
