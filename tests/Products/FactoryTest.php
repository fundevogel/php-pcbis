<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests\Api;

use Pcbis\Webservice;
use Pcbis\Exceptions\UnknownTypeException;
use Pcbis\Products\Factory;

use PHPUnit\Framework\TestCase;


class FactoryTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * @var \Pcbis\Webservice
     */
    private static $object;


    // /**
    //  * Available ISBNs
    //  *
    //  * @var array
    //  */
    // private static $available = [
    //     '978-3-95854-151-1',  # Der Stein und das Meer
    //     '978-3-314-10561-6',  # Die Tode meiner Mutter
    //     '978-3-407-75554-4',  # Helsin Apelsin und der Spinner
    //     '978-3-95640-221-0',  # Die Hundebande in Paris
    //     '978-3-95640-223-4',  # Q-R-T: Nächste Stunde: Außerirdisch
    // ];


    // /**
    //  * Unavailable ISBNs
    //  *
    //  * @var array
    //  */
    // private static $unavailable = [
    //     '978-3-596-80479-5',  # Ein Haufen Ärger
    //     '978-3-570-40342-6',  # We All Looked Up
    // ];


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

    public function testFactory(): void
    {
        # Setup
        # (1) Product mockups
        $products = [
            '\Pcbis\Products\Books\Types\Ebook' => ['Sortimentskennzeichen' => 'AG'],
            '\Pcbis\Products\Books\Types\Hardcover' => ['Sortimentskennzeichen' => 'HC'],
            '\Pcbis\Products\Books\Types\Schoolbook' => ['Sortimentskennzeichen' => 'SB'],
            '\Pcbis\Products\Books\Types\Softcover' => ['Sortimentskennzeichen' => 'TB'],
            '\Pcbis\Products\Media\Types\Audiobook' => ['Sortimentskennzeichen' => 'AC'],
            '\Pcbis\Products\Media\Types\Movie' => ['Sortimentskennzeichen' => 'AD'],
            '\Pcbis\Products\Media\Types\Music' => ['Sortimentskennzeichen' => 'AK'],
            '\Pcbis\Products\Media\Types\Sound' => ['Sortimentskennzeichen' => 'AF'],
            '\Pcbis\Products\Nonbook\Types\Boardgame' => ['Sortimentskennzeichen' => 'AN'],
            '\Pcbis\Products\Nonbook\Types\Calendar' => ['Sortimentskennzeichen' => 'AI'],
            '\Pcbis\Products\Nonbook\Types\Map' => ['Sortimentskennzeichen' => 'AJ'],
            '\Pcbis\Products\Nonbook\Types\Nonbook' => ['Sortimentskennzeichen' => 'AB'],
            '\Pcbis\Products\Nonbook\Types\Notes' => ['Sortimentskennzeichen' => 'AL'],
            '\Pcbis\Products\Nonbook\Types\Software' => ['Sortimentskennzeichen' => 'AE'],
            '\Pcbis\Products\Nonbook\Types\Stationery' => ['Sortimentskennzeichen' => 'AM'],
            '\Pcbis\Products\Nonbook\Types\Toy' => ['Sortimentskennzeichen' => 'AO'],
            '\Pcbis\Products\Nonbook\Types\Videogame' => ['Sortimentskennzeichen' => 'AH'],
        ];

        foreach ($products as $type => $array) {
            # Run function
            $result = Factory::factory($array, [
                'api' => self::$object,
                'isbn' => '123-456-78-9',
                'fromCache' => false,
                'translations' => [],
            ]);

            # Assert result
            $this->assertTrue(is_a($result, $type));
        }
    }


    public function testFactoryDefault(): void
    {
        $result = Factory::factory([], [
            'api' => self::$object,
            'isbn' => '123-456-78-9',
            'fromCache' => false,
            'translations' => [],
        ]);

        # Assert result
        $this->assertTrue(is_a($result, '\Pcbis\Products\Books\Types\Hardcover'));
    }


    public function testFactoryInvalid(): void
    {
        # Assert exception
        $this->expectException(UnknownTypeException::class);

        # Run function
        $result = Factory::factory(['Sortimentskennzeichen' => 'XXX'], [
            'api' => self::$object,
            'isbn' => '123-456-78-9',
            'fromCache' => false,
            'translations' => [],
        ]);
    }
}
