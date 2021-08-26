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


class BoardgameTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Boardgame EANs
     *
     * @var array
     */
    private static $eans = [
        '5010993411504',  # Monopoly Classic
        '4002051693602',  # Die Siedler von Catan
        '4002051694104',  # Die Siedler von Catan, Seefahrer
        '4002051695101',  # Die Siedler von Catan, Städte & Ritter
        '4002051693305',  # Die Siedler von Catan, Händler & Barbaren
        '4002051694111',  # Die Siedler von Catan, Entdecker & Piraten
        '4015566000964',  # Maus und Mystik
        '0681706117010',  # Maus und Mystik, Herz des Glürm
        '4015566033108',  # Maus und Mystik, Geschichten aus dem Dunkelwald
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
            'Spieleranzahl',
            'Spieldauer',
        ];

        foreach (self::$eans as $ean) {
            # Run function
            $result = self::$object->load($ean);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Nonbook\Types\Boardgame'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(16, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
