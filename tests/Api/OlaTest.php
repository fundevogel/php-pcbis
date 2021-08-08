<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Api;

use Pcbis\Webservice;

use PHPUnit\Framework\TestCase;


class OlaTest extends TestCase
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
        $credentials = json_decode(file_get_contents(__DIR__ . '/../../login.json'), true);

        # (2) Global object
        self::$object = new Webservice($credentials);
    }


    /**
     * Tests
     */

    public function testShowSource(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->showSource();

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\stdClass'));
        }
    }


    public function testHasOlaCode(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->hasOlaCode();

            # Assert result
            $this->assertIsBool($result);
        }
    }


    public function testOlaCode(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->olaCode();

            # Assert result
            $this->assertIsString($result);
        }
    }


    public function testHasOlaMessage(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->hasOlaMessage();

            # Assert result
            $this->assertIsBool($result);
        }
    }


    public function testOlaMessage(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->olaMessage();

            # Assert result
            $this->assertIsString($result);
        }
    }


    public function testHasErrorCode(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->hasErrorCode();

            # Assert result
            $this->assertIsBool($result);
        }
    }


    public function testErrorCode(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->errorCode();

            # Assert result
            $this->assertIsString($result);
        }
    }


    public function testHasErrorMessage(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->hasErrorMessage();

            # Assert result
            $this->assertIsBool($result);
        }
    }


    public function testErrorMessage(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->errorMessage();

            # Assert result
            $this->assertIsString($result);
        }
    }


    public function testIsAvailable(): void
    {
        foreach (self::$available as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->isAvailable();

            # Assert result
            $this->assertIsBool($result);
        }
    }


    public function testIsUnavailable(): void
    {
        foreach (self::$unavailable as $isbn) {
            # Run function
            $result = self::$object->ola($isbn)->isUnavailable();

            # Assert result
            $this->assertTrue($result);
        }
    }
}
