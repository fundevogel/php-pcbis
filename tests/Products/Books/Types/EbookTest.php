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


class EbookTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Ebook EANs/ISBNs
     *
     * @var array
     */
    private static $isbns = [
        '978-3-522-62111-3',  # Momo
        '978-3-522-62112-0',  # Die unendliche Geschichte
        '978-3-522-61046-9',  # Jim Knopf und Lukas der Lokomotivführer
        '978-3-522-61088-9',  # Jim Knopf und die Wilde 13
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
            'Abmessungen',  # TODO: ??
            'Sprachen',
            'AutorIn',
            'Vorlage',
            'IllustratorIn',
            'ZeichnerIn',
            'PhotographIn',
            'ÜbersetzerIn',
            'HerausgeberIn',
            'MitarbeiterIn',
            'Kategorien',
            'Themen',
            'Reihe',
            'Band',
            'Einband',
            'Seitenzahl',
            'Antolin',
            'Unterstützt',
            'Printausgabe',
            'Dateigröße',
            'Dateiformat',
            'DRM',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Books\Types\Ebook'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(29, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
