<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products\Nonbook\Types;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class SoftwareTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Software EANs
     *
     * @var array
     */
    private static $isbns = [
        '978-3-648-13942-4',  # Lexware FinanzManager 2021
        '978-3-648-14359-9',  # Lexware hausverwalter 2021
        '978-3-86681-495-0',  # Fritz & Fertig, Sonderedition 2 in 1
        '978-3-8032-4328-7',  # Goldfinger Junior 7, 1 CD-ROM
        '978-3-8032-4327-0',  # Goldfinger Junior 6, 1 CD-ROM
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
            'Gewicht',
            'Abmessungen',
            'Sprachen',
            'Kategorien',
            'Themen',
            'Version',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Nonbook\Types\Software'));

            $this->assertIsBool($result->isEducational());

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(15, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
