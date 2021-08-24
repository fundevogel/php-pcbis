<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products\Media;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class MediumTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Media EANs/ISBNs
     *
     * @var array
     */
    private static $isbns = [
        '4006680067889',  # Momo, 1 DVD
        '4006680067872',  # Momo, 1 BluRay
    ];


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Login credentials
        $credentials = json_decode(file_get_contents(__DIR__ . '/../../../login.json'), true);

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
            'Gewicht',
            'Abmessungen',
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
            'Dauer',
            'KomponistIn',
            'RegisseurIn',
            'ProduzentIn',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Media\Medium'));

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
