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


class MovieTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Audiobook EANs/ISBNs
     *
     * @var array
     */
    private static $isbns = [
        '5051890318947',  # Die Verurteilten (DVD)
        '5051890318930',  # Die Verurteilten (Blu-ray)
        '4010884537970',  # Der Pate I (DVD)
        '4010884250763',  # Der Pate I (Blu-ray)
        '4010884592719',  # Der Pate I - III (DVD-Box)
        '5053083238902',  # Pulp Fiction (Blu-ray)
        '5053083238919',  # Pulp Fiction (DVD)
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
            'SchauspielerIn',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Media\Types\Movie'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(27, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
