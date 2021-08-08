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


class AudiobookTest extends TestCase
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
        # 2021-02
        '978-3-8373-1191-4',
        '978-3-7424-2120-3',
        '978-3-9816539-7-7',
        '978-3-941009-80-6',
        '978-3-7424-2055-8',
        '978-3-8445-4320-9',
        '978-3-7424-2071-8',

        # 2021-01
        '978-3-7456-0212-8',
        '978-3-7456-0262-3',
        '978-3-7424-1803-6',
        '978-3-8373-1188-4',
        '978-3-7456-0269-2',
        '978-3-8373-1182-2',
        '978-3-8337-4282-8',
        '978-3-7424-1855-5',
        '978-3-96346-041-8',
        '978-3-96346-042-5',
        '978-3-7456-0191-6',
        '978-3-7424-1651-3',
        '978-3-7456-0265-4',
        '978-3-7456-0288-3',
        '978-3-7313-1286-4',
        '978-3-8373-1179-2',
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
            'Inhaltsbeschreibung',
            'Preis',
            'Erscheinungsjahr',
            'Altersempfehlung',
            'AutorIn',
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
            'Verlag',
            'Reihe',
            'Band',
            'ErzählerIn',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Media\Types\Audiobook'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(23, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
