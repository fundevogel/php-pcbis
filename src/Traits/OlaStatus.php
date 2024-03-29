<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Traits;

/**
 * Trait OlaStatus
 *
 * Provides ability to work with OLA codes & messages
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
     * - 18 Wird besorgt – nicht remittierbar/nicht stornierbar
     * - 97 Print on Demand (ggf. mit Angabe der Lieferzeit) – nicht remittierbar/nicht stornierbar
     *
     * Preorder possible:
     * - 11 Erscheint laut Verlag/Lieferant .../... in neuer Auflage/als Nachfolgeprodukt
     * - 12 Nachdruck/wird nachproduziert. Folgt laut Verlag/Lieferant .../...
     * - 15 Fehlt kurzfristig am Lager
     * - 21 Noch nicht erschienen. Erscheint laut Verlag/Lieferant ...
     * - 23 Titel wegen Lieferverzug des Verlags/der Verlagsauslieferung derzeit nicht lieferbar
     * - 25 Artikel neu aufgenommen. Noch nicht am Lager
     * - 80 Fehlt, da der Verlag/Lieferant derzeit nicht liefern kann
     * - 98 Folgt mit nächster Lieferung
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
     * No orders possible:
     * - 07 Vergriffen, keine Neuauflage, Bestellung abgelegt
     * - 17 Führen wir nicht bzw. nicht mehr
     * - 19 Ladenpreis aufgehoben. Führen wir nicht mehr
     * - 20 Noch nicht erschienen. Bestellung nicht vorgemerkt
     * - 24 Erscheint nicht laut Verlag/Lieferant
     * - 28 Titelnummer unbekannt
     * - 29 ISBN oder EAN unbekannt
     * - 43 Vergriffen – Neuauflage/Nachfolgeprodukt unbestimmt – Bestellung wird nicht vorgemerkt
     * - 60 Indiziert. Führen wir nicht mehr
     * - 62 Artikel infolge rechtlicher Auseinandersetzungen zur Zeit nicht lieferbar. Bestellung nicht vorgemerkt
     * - 88 Konditionsänderung durch den Verlag/Lieferanten. Führen wir nicht mehr
     * - 94 Wird zur Zeit nur ab Verlag/Lieferant geliefert – Bestellung nicht vorgemerkt
     * - 99 Titel hat Nachfolgetitel/-auflage
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
     * Current KNV 'Meldenummer' descriptions
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
     * Current KNV 'Fehlernummer' descriptions
     *
     * @var array
     */
    protected array $errorMessages = [
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
}
