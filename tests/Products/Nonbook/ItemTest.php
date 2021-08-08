<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class ItemTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Calendar EANs/ISBNs
     *
     * @var array
     */
    private static $isbns = [
        # 2021/22
        '978-3-7795-0660-7',
        '978-3-7795-0659-1',
        '4250809647876',
        '978-3-0360-5022-5',
        '4251732322861',
        '978-3-95878-040-8',
        '4250809648095',
        '978-3-95878-041-5',
        '978-3-7160-9410-5',
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
            'Inhaltsbeschreibung',
            'Preis',
            'Erscheinungsjahr',
            'Altersempfehlung',
        ];

        foreach (self::$isbns as $isbn) {
            # Run function
            $result = self::$object->load($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Nonbook\Item'));

            $result = $result->export();

            $this->assertIsArray($result);
            $this->assertCount(6, $result);

            foreach ($keys as $index => $key) {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($keys[$index], array_keys($result)[$index]);
            }
        }
    }
}
