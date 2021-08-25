<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products\Media\Types;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class MusicTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Music EANs/ISBNs
     *
     * @var array
     */
    private static $isbns = [
        '9783839845998',  # Fredrik Vahle - Best Of
        '9783839845677',  # Fredrik Vahle - Anne Kaffeekanne
        '0738572138127',  # Harry Potter - Complete Film Music
        '4009910455227',  # Judas Priest - Sad Wings Of Destiny
        '0190295567583',  # Iron Maiden - The Book Of Souls
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
            'Dauer',
            'KomponistIn',
            'RegisseurIn',
            'ProduzentIn',
            'AutorIn',
            'Vorlage',
            'IllustratorIn',
            'ZeichnerIn',
            'PhotographIn',
            'ÜbersetzerIn',
            'HerausgeberIn',
            'MitarbeiterIn',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Media\Types\Music'));

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
