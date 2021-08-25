<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Products;

use Pcbis\Webservice;
use Pcbis\Products\Product;

use PHPUnit\Framework\TestCase;


class ProductTest extends TestCase
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
        foreach (self::$isbns as $isbn) {
            # Setup
            # (1) Source data
            $source = self::$object->fetch($isbn);

            # (2) Properties
            $props = [
                'api'          => self::$object,
                'isbn'         => $isbn,
                'fromCache'    => $source['fromCache'],
                'translations' => [],
                'type'         => '',
            ];

            # Run function
            $result = $this->getMockForAbstractClass(Product::class, [$source, $props]);

            # Assert result
            # TODO: Migrate to `assertInstanceOf`
            $this->assertTrue(is_a($result, '\Pcbis\Products\Product'));

            $this->assertIsArray($result->showSource());
            $this->assertIsBool($result->fromCache());

            $this->assertIsBool($result->hasDowngrade());
            $this->assertTrue(is_a($result->downgrade(), '\Pcbis\Products\Product'));

            $this->assertIsBool($result->hasUpgrade());
            $this->assertTrue(is_a($result->upgrade(), '\Pcbis\Products\Product'));

            $this->assertTrue(is_a($result->ola(), '\Pcbis\Api\Ola'));

            $this->assertEquals($result->isbn(), $isbn);
            $this->assertIsString($result->isbn());

            $this->assertIsString($result->type());

            $this->assertIsString($result->title());
            $this->assertIsString($result->subtitle());
            $this->assertIsString($result->publisher());
            $this->assertIsArray($result->publisher(true));
            $this->assertIsString($result->publisher(false));
            $this->assertIsString($result->description());
            $this->assertIsArray($result->description(true));
            $this->assertIsString($result->description(false));
            $this->assertIsString($result->retailPrice());
            $this->assertIsString($result->releaseYear());
            $this->assertIsString($result->age());
            $this->assertIsBool($result->isSeries());
            $this->assertIsString($result->series());
            $this->assertIsArray($result->series(true));
            $this->assertIsString($result->series(false));
            $this->assertIsString($result->volume());
            $this->assertIsArray($result->volume(true));
            $this->assertIsString($result->volume(false));
            $this->assertIsString($result->weight());
            $this->assertIsString($result->dimensions());
            $this->assertIsString($result->languages());
            $this->assertIsArray($result->languages(true));
            $this->assertIsString($result->languages(false));
            $this->assertIsString($result->categories());
            $this->assertIsArray($result->categories(true));
            $this->assertIsString($result->categories(false));
            $this->assertIsString($result->topics());
            $this->assertIsArray($result->topics(true));
            $this->assertIsString($result->topics(false));

            $this->assertIsBool($result->isAvailable());
            $this->assertIsBool($result->isUnavailable());
        }
    }
}
