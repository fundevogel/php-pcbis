<?php

namespace Pcbis\Traits;

use Pcbis\Helpers\Butler;


/**
 * Trait OlaStatus
 *
 * Provides ability to work with OLA codes & messages
 *
 * @package PHPCBIS
 */

trait OlaStatus
{
    /**
     * Properties
     */

    /**
     * Status codes of available products
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
    protected $available = [
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
     * Status codes of unavailable products
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
    protected $unavailable = [
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
     * All KNV 'Meldenummer' descriptions
     *
     * @var array
     */
    protected $olaMessages = [
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
     * Current KNV 'Meldenummer'
     *
     * @var string
     */
    protected $olaCode = null;


    /**
     * Current KNV 'Meldenummer' description
     *
     * @var string
     */
    protected $olaMessage = null;


    /**
     * Methods
     */

    /**
     * Checks if KNV 'Meldenummer' is available
     *
     * @return bool
     */
    public function hasOlaCode(): bool
    {
        return $this->olaCode !== null;
    }


    /**
     * Returns current KNV 'Meldenummer'
     *
     * @return string
     */
    public function olaCode(): string
    {
        if ($this->hasOlaCode()) {
            return $this->olaCode;
        }

        return '';
    }


    /**
     * Checks if KNV 'Meldenummer' description is available
     *
     * @return bool
     */
    public function hasOlaMessage(): bool
    {
        return $this->olaMessage !== null;
    }


    /**
     * Returns current KNV 'Meldenummer' description
     *
     * @return string
     */
    public function olaMessage(): string
    {
        if ($this->hasOlaMessage()) {
            return $this->olaMessage;
        }

        return '';
    }
}
