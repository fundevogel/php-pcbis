<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products\Books\Types;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class SchoolbookTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Schoolbook ISBNs
     *
     * @var array
     */
    private static $isbns = [
        # Englisch
        '978-3-12-835025-7',  # Green Line 2 G9 - 6. Klasse, Workbook
        '978-3-12-835035-6',  # Green Line 3 G9 - 7. Klasse, Workbook mit Audios
        '978-3-12-578153-5',  # The Hunger Games, Text in Englisch für das 3., 4. und 5. Lernjahr

        # Französisch
        '978-3-12-624016-1',  # Découvertes 1. Ausgabe 1. oder 2. Fremdsprache, m. 1 Beilage
        '978-3-12-623612-6',  # Tous ensemble. Ausgabe ab 2013

        # Russisch
        '978-3-12-527528-7',  # Konetschno! 1, Russisch als 2. oder 3. Fremdsprache
        '978-3-12-527679-6',  # Jasno! neu Übungsbuch A1-A2

        # Spanisch
        '978-3-12-538003-5',  # ¡Adelante! Nivel elemental, m. 1 Beilage
        '978-3-06-121646-7',  # Encuentros - Método de Español - 3. Fremdsprache - Hoy - Band 3

        # Italienisch
        '978-3-19-275427-2',  # Chiaro! A1 - Nuova edizione
        '978-3-19-505438-6',  # Espresso ragazzi 1, Corso di italiano

        # Latein
        '978-3-637-01549-4',  # Stowasser - Neubearbeitung
        '978-3-661-40202-4',  # Cursus - Neue Ausgabe, Arbeitsheft 1 mit Lösungen
        '978-3-661-40001-3',  # ROMA A Begleitband

        # Griechisch
        '978-3-12-606650-1',  # Kalimera, Neubearbeitung, Lehrbuch, m. 2 Audio-CDs
        '978-3-12-663212-6',  # Kantharos, Ausgabe ab 2018
        '978-3-12-663216-4',  # Grammateion

        # Deutsch
        '978-3-14-022287-7',  # Nathan der Weise
        '978-3-06-067560-9',  # Deutschbuch Gymnasium - Nordrhein-Westfalen - Neue Ausgabe - 6. Schuljahr
        '978-3-637-00874-8',  # Deutsch-Stars - Allgemeine Ausgabe - 2. Schuljahr

        # Mathematik
        '978-3-619-25454-5',  # Das Übungsheft Mathematik Klasse 2
        '978-3-619-45454-9',  # Das Übungsheft Mathematik Klasse 4

        # Biologie
        '978-3-14-117030-6',  # Erlebnis Biologie, Allgemeine Ausgabe 2019
        '978-3-661-03009-8',  # Biologie - Bayern 9

        # Chemie
        '978-3-7585-8011-6',  # Chemie FOS Bayern Jahrgangsstufe 11
        '978-3-12-756141-8',  # Elemente Chemie, 7-10 Schuljahr

        # Physik
        '978-3-12-069310-9',  # PRISMA Physik. Differenzierende Ausgabe A
        '978-3-12-772971-9',  # Impulse Physik. Ausgabe G9 für Nordrhein-Westfalen ab 2019

        # Informatik
        '978-3-661-38023-0',  # Aufbaukurs Informatik GY Baden-Württemberg
        '978-3-425-04553-5',  # Medienwelten, Arbeitsheft, Arbeitsheft 3 Informatik

        # Geographie
        '978-3-12-105202-8',  # Terra Erdkunde 2
        '978-3-14-100800-5',  # Weltatlas
        '978-3-12-104716-1',  # Terra Geographie Kursstufe
        '978-3-14-144890-0',  # Diercke Geografie, Ausgabe 2016 für Gymnasien in Berlin und Brandenburg

        # Sachunterricht
        '978-3-523-80501-7',  # Heimat- und Sachkunde, Arbeitshefte, Ausgabe Thüringen, 1. Schuljahr
        '978-3-06-109317-4',  # Schau dich um und mach mit, Heimatkunde- und Sachkundeunterricht
        '978-3-12-310841-9',  # Bücherwurm Sachunterricht. Ausgabe ab 2019, 1. Klasse, Sachheft Sachsen

        # Geschichte
        '978-3-14-112131-5',  # Horizonte Geschichte, Ausgabe 2018 für Realschulen in Bayern
        '978-3-14-035726-5',  # Geschichte - Ausgabe für Gymnasien in Bayern

        # Gemeinschaftskunde
        '978-3-661-72068-5',  # Gemeinschaftskunde 11/12
        '978-3-14-101436-5',  # Demokratie heute - Ausgabe 2019 für Sachsen, 9. Klasse

        # Sozialkunde
        '978-3-14-023826-7',  # Politik erleben - Sozialkunde - Stammausgabe
        '978-3-14-116646-0',  # Forum - Wirtschaft und Recht

        # Wirtschaftskunde
        '978-3-12-882741-4',  # Wirtschaftskunde. Ausgabe 2021
        '978-3-8085-4661-1',  # Wirtschaft im Blick

        # Hauswirtschaft
        '978-3-582-14392-1',  # Arbeitsblätter Hauswirtschaft
        '978-3-427-87601-4',  # Lernfelder Hauswirtschaft - 1. Ausbildungsjahr: Schülerband

        # Musik
        '978-3-86227-098-9',  # Gesangsklasse, Schülerheft
        '978-3-95660-077-7',  # Bedrich Smetana - Die Moldau

        # Kunst
        '978-3-8490-3644-7',  # Realschule 2019 - Bayern - Kunsterziehung
        '978-3-8344-3878-2',  # Kinder entdecken Hundertwasser

        # Ethik
        '978-3-14-025416-8',  # Fair Play - 5./6. Schuljahr, Schülerband
        '978-3-12-695309-2',  # Leben leben, Ausgabe Baden-Württemberg ab 2017, Ethik.

        # Religion
        '978-3-86189-187-1',  # Religionen unserer Welt, Ihre Bedeutung in Geschichte, Kultur und Alltag
        '978-3-12-007266-9',  # Leben gestalten 1. Ausgabe, Katholischer Religionsunterricht
        '978-3-464-81484-0',  # Kinder fragen nach dem Leben - Evangelische Religion
        '978-3-7101-0724-5',  # Islamstunde 5 - Buch.   Religionsbuch für die Sekundarstufe I
        '978-3-507-01763-4',  # Bismillah - Wir entdecken den Islam

        # Sonstiges
        '978-3-403-29070-4',  # Ampelkarten
        '978-3-7627-4190-9',  # Klappbares Periodensystem der Elemente
    ];


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Login credentials
        $credentials = json_decode(file_get_contents(__DIR__ . '/../../../../login.json'), true);

        # (2) Global object
        self::$object = new Webservice($credentials);
    }


    /**
     * Tests
     */

    public function testExport(): void
    {
        # Setup
        # (1) Keys
        $keys = [
            'Titel',
            'Untertitel',
            'Verlag',
            'Inhaltsbeschreibung',
            'Preis',
            'Erscheinungsjahr',
            'Altersempfehlung',
            'Reihe',
            'Band',
            'Gewicht',  # TODO: ??
            'Abmessungen',  # TODO: ??
            'Sprachen',
            'Kategorien',
            'Themen',
            'Einband',
            'Seitenzahl',
            'Antolin',
            'AutorIn',
            'Vorlage',
            'IllustratorIn',
            'ZeichnerIn',
            'PhotographIn',
            'ÜbersetzerIn',
            'HerausgeberIn',
            'MitarbeiterIn',
            'Schulfach',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Books\Types\Schoolbook'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(26, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
