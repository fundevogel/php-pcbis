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
     * Current KNV 'Meldenummer' description
     *
     * @var string
     */
    private $statusMessage = null;


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

        if (isset($data->Meldenummer)) {
            $this->statusCode = (string) $data->Meldenummer;

            if (array_key_exists($this->statusCode, $this->statusMessages)) {
                $this->statusMessage = $this->statusMessages[$this->statusCode];
            }
        }

        if (isset($data->Fehlercode)) {
            $this->errorCode = $data->Fehlercode;
        }

        if (isset($data->Fehlertext)) {
            $this->errorMessage = $data->Fehlertext;
        }
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
     * @return string|null
     */
    public function statusCode()
    {
        return $this->statusCode;
    }


    /**
     * Checks if KNV 'Meldenummer' description is available
     *
     * @return bool
     */
    public function hasStatusMessage(): bool
    {
        if ($this->hasStatusCode()) {
            return array_key_exists($this->statusCode, $this->statusMessages);
        }

        return false;
    }


    /**
     * Prints current KNV 'Meldenummer' description
     *
     * @return string|null
     */
    public function statusMessage()
    {
        if (array_key_exists($this->statusCode, $this->statusMessages)) {
            return $this->statusMessages[$this->statusCode];
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
