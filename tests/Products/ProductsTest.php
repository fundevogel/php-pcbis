<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products;

use Pcbis\Webservice;
use Pcbis\Products\Products;

use PHPUnit\Framework\TestCase;


class ProductsTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    /**
     * ISBNs
     *
     * @var array
     */
    private static $isbns = [
        '978-3-95854-151-1',  # Der Stein und das Meer
        '978-3-314-10561-6',  # Die Tode meiner Mutter
        '978-3-407-75554-4',  # Helsin Apelsin und der Spinner
        '978-3-95640-221-0',  # Die Hundebande in Paris
        '978-3-95640-223-4',  # Q-R-T: Nächste Stunde: Außerirdisch
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
        # Setup
        # (1) Data
        $items = [];

        foreach (self::$isbns as $isbn) {
            $items[] = self::$object->load($isbn);
        }

        # Run function
        $result = $this->getMockForAbstractClass(Products::class, [$items]);

        # Assert result
        $this->assertTrue($result->isOdd());
        $this->assertEquals($result->count(), count($items));
        $this->assertEquals($result->pluck('isbn'), self::$isbns);
        $this->assertEquals($result->current(), $items[0]);
        $this->assertEquals($result->next(), $items[1]);
        $this->assertEquals($result->last(), end($items));
        $this->assertEquals($result->first(), $items[0]);
    }
}
