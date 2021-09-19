<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products\Books;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class BookTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Picture books ISBNs
     *
     * @var array
     */
    private static $isbns = [
        # 2021-02
        '978-3-458-17956-6',
        '978-3-8337-4343-6',
        '978-3-89565-411-4',
        '978-3-96185-560-5',
        '978-3-95854-166-5',
        '978-3-7913-7493-2',
        '978-3-570-17922-2',
        '978-3-7373-5769-2',
        '978-3-8251-5284-0',
        '978-3-522-45960-0',
        '978-3-7117-4023-6',
        '978-3-407-75494-3',
        '978-3-407-75498-1',
        '978-3-257-01294-1',
        '978-3-7026-5956-1',
        '978-3-7152-0796-4',
        '978-3-96704-708-0',
        '978-3-86429-529-4',
        '978-3-314-10573-9',
        '978-3-8489-0197-5',
        '978-3-03876-193-8',
        '978-3-314-10578-4',
        '978-3-95614-466-0',
        '978-3-8458-4359-9',
        '978-3-948722-12-8',
        '978-3-407-75497-4',
        '978-3-551-52128-6',
        '978-3-7373-5842-2',
        '978-3-458-17955-9',
        '978-3-458-17855-2',
        '978-3-522-45937-2',
        '978-3-7348-2078-6',
        '978-3-7725-2915-3',
        '978-3-314-10583-8',
        '978-3-314-10577-7',
        '978-3-945530-37-5',
        '978-3-522-45953-2',
        '978-3-8369-6121-9',
        '978-3-407-75496-7',
        '978-3-7348-2075-5',
        '978-3-86429-515-7',
        '978-3-85535-667-6',
        '978-3-95470-159-9',
        '978-3-8369-6145-5',
        '978-3-8458-4114-4',
        '978-3-86429-516-4',
        '978-3-522-45977-8',
        '978-3-95728-524-9',
        '978-3-407-76246-7',
        '978-3-314-10576-0',
        '978-3-7891-4808-8',
        '978-3-95939-099-6',
        '978-3-96428-117-3',
        '978-3-95728-525-6',
        '978-3-7432-1116-2',
        '978-3-257-01261-3',
        '978-3-96843-013-3',
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
            'Reihe',
            'Band',
            'Gewicht',
            'Abmessungen',
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
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Books\Book'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(25, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
