<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products;

use Pcbis\Webservice;
use Pcbis\Products\ProductList;

use PHPUnit\Framework\TestCase;


class ProductListTest extends TestCase
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

    public function testBasics(): void
    {
        # Run function
        $result = self::$object->load(self::$available);

        # Assert result
        $this->assertInstanceOf('\Pcbis\Products\ProductList', $result);

        foreach (self::$available as $isbn) {
            $this->assertInstanceOf('\Pcbis\Products\Product', $result->getISBN($isbn));
        }

        foreach (self::$unavailable as $isbn) {
            $this->assertFalse($result->getISBN($isbn));
        }
    }
}
