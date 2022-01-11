<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests;

use Pcbis\Webservice;
use Pcbis\Exceptions\InvalidISBNException;
use Pcbis\Exceptions\InvalidLoginException;
use Pcbis\Exceptions\NoRecordFoundException;

use PHPUnit\Framework\TestCase;


class WebserviceTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * Available ISBNs
     *
     * @var array
     */
    private static $available = [
        '978-3-95854-151-1',  # Der Stein und das Meer
        '978-3-314-10561-6',  # Die Tode meiner Mutter
        '978-3-407-75554-4',  # Helsin Apelsin und der Spinner
        '978-3-95640-221-0',  # Die Hundebande in Paris
        '978-3-95640-223-4',  # Q-R-T: Nächste Stunde: Außerirdisch
    ];


    /**
     * Unavailable ISBNs
     *
     * @var array
     */
    private static $unavailable = [
        '978-3-596-80479-5',  # Ein Haufen Ärger
        '978-3-570-40342-6',  # We All Looked Up
    ];


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Login credentials
        $credentials = json_decode(file_get_contents(__DIR__ . '/../login.json'), true);

        # (2) Global object
        self::$object = new Webservice($credentials);
    }


    /**
     * Tests
     */

    public function testLoginInvalid(): void
    {
        # Setup
        # (1) Invalid login credentials
        $array = [
            'VKN'      => '123',
            'Benutzer' => '123',
            'Passwort' => '123',
        ];

        # Assert exception
        $this->expectException(InvalidLoginException::class);

        # Run function
        $result = new Webservice($array);
    }


    public function testValidate(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->validate($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertEquals($isbn, $result);
            $this->assertIsString($result);
        }
    }


    public function testValidateInvalid(): void
    {
        # Setup
        # (1) Invalid ISBNs
        $invalid = [
            '123-4545-67888-9',  # invalid start of string
            'invalidisbn',       # letters instead of numbers
            '1122334455667788',  # too long
            '1234567890',        # too short
        ];

        # Assert exception
        $this->expectException(InvalidISBNException::class);

        # Run function
        foreach ($invalid as $isbn) {
            $result = self::$object->validate($isbn);
        }
    }


    public function testOla(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Api\Ola'));
        }
    }


    public function testFetch(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->fetch($isbn, true);

            # Assert result
            $this->assertIsArray($result);
            $this->assertCount(2, $result);

            $this->assertArrayHasKey('fromCache', $result);
            $this->assertFalse($result['fromCache']);

            $this->assertArrayHasKey('source', $result);
            $this->assertIsArray($result['source']);

            # Run function
            $result = self::$object->fetch($isbn, false);

            # Assert result
            $this->assertIsArray($result);
            $this->assertCount(2, $result);

            $this->assertArrayHasKey('fromCache', $result);
            $this->assertTrue($result['fromCache']);

            $this->assertArrayHasKey('source', $result);
            $this->assertIsArray($result['source']);
        }
    }


    public function testLoad(): void
    {
        # Run function #1
        foreach (self::$available as $isbn) {
            $result = self::$object->load($isbn);

            # Assert result
            $this->assertInstanceOf('\Pcbis\Products\Product', $result);
        }

        # Run function #2
        $result = self::$object->load(self::$available);

        # Assert result
        $this->assertInstanceOf('\Pcbis\Products\ProductList', $result);
    }


    public function testLoadInvalid(): void
    {
        # Assert exception
        $this->expectException(NoRecordFoundException::class);

        # Run function #1
        foreach (self::$unavailable as $isbn) {
            $result = self::$object->load($isbn);
        }

        # Run function #2
        $result = self::$object->load(self::$unavailable);
    }
}
