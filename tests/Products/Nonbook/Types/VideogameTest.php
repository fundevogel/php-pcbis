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


class VideogameTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Videogame EANs
     *
     * @var array
     */
    private static $eans = [
        '3307216066583',  # Assassin's Creed Odyssey (PC)
        '5055277037773',  # Humankind (First Day Edition) (PC)
        '5026555420013',  # NBA 2K18 (PS3)
        '5021290080928',  # Shadow of the Tomb Raider (PS4)
        '5051890322609',  # LEGO - Star Wars, Die Skywalker Saga (PS5)
        '0045496420093',  # The Legend of Zelda, Breath of the Wild (Switch)
        '0045496333218',  # Mario Kart 8 (Nintendo Wii U)
        '0045496400156',  # Legend of Zelda, Twilight Princess (Nintendo Wii)
        '4018281677107',  # Teenage Mutant Ninja Turtles (Xbox 360)
        '5026555359160',  # NBA 2K18 (Xbox One)
        '5021290091351',  # Life is Strange, True Colors, (Xbox Series X)
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
            'Plattformen',
        ];

        foreach (self::$eans as $ean) {
            # Run function
            $result = self::$object->load($ean);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Nonbook\Types\Videogame'));

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
