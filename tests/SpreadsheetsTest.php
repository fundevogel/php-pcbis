<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests;

use Pcbis\Spreadsheets;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;


class SpreadsheetsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * Path to fixtures
     *
     * @var string
     */
    private static $fixturePath;


    /**
     * Setup (global)
     */

    public static function setUpBeforeClass(): void
    {
        # Define fixture directory
        self::$fixturePath = __DIR__ . '/fixtures/SpreadsheetsTest';
    }


    /**
     * Tests
     */

    public function testCsvOpen(): void
    {
        # Setup
        # (1) Headers
        $headers = [
            'AutorIn',
            'Titel',
            'Verlag',
            'ISBN',
            'Einband',
            'Preis',
            'Meldenummer',
            'SortRabatt',
            'Gewicht',
            'Informationen',
            'Zusatz',
            'Kommentar'
        ];

        # (2) File path
        $file = self::$fixturePath . '/csv2array.csv';

        # Run function
        $result = Spreadsheets::csvOpen($file, $headers);

        # Assert result
        $this->assertIsArray($result);
        $this->assertCount(5, $result);

        foreach ($result as $entry) {
            $this->assertIsArray($entry);
            $this->assertCount(12, $entry);

            foreach ($headers as $header) {
                $this->assertArrayHasKey($header, $entry);
            }
        }
    }


    public function testCsv2Array(): void
    {
        # Setup
        # (1) Headers
        $headers = [
            'AutorIn',
            'Titel',
            'Untertitel',
            'Verlag',
            'Mitwirkende',
            'Preis',
            'Erscheinungsjahr',
            'ISBN',
            'Altersempfehlung',
            'Inhaltsbeschreibung',
            'Informationen',
            'Einband',
            'Seitenzahl',
            'Abmessungen',
        ];

        # (2) File path
        $file = self::$fixturePath . '/csv2array.csv';

        # Run function
        $result = Spreadsheets::csv2array($file);

        # Assert result
        $this->assertIsArray($result);
        $this->assertCount(5, $result);

        foreach ($result as $entry) {
            $this->assertIsArray($entry);
            $this->assertCount(14, $entry);

            foreach ($headers as $index => $header) {
                $this->assertArrayHasKey($header, $entry);
                $this->assertEquals($headers[$index], array_keys($entry)[$index]);
            }
        }
    }


    public function testArray2csv(): void
    {
        # Setup
        # (1) Source array
        $array = [
            [
                'A' => rand(1, 10),
                'B' => rand(1, 10),
                'C' => rand(1, 10),
                'D' => rand(1, 10),
            ],
            [
                'A' => rand(11, 20),
                'B' => rand(11, 20),
                'C' => rand(11, 20),
                'D' => rand(11, 20),
            ],
            [
                'A' => rand(21, 30),
                'B' => rand(21, 30),
                'C' => rand(21, 30),
                'D' => rand(21, 30),
            ],
        ];

        # (2) Virtual directory
        $root = vfsStream::setup('home');

        # (3) File path
        $file = vfsStream::url('home/test_array2csv.csv');

        # Run function
        $result = Spreadsheets::array2csv($array, $file);

        # Assert result
        $this->assertTrue($result);
        $this->assertFileExists($file);
        $this->assertTrue(self::isUTF8($file));
    }


    /**
     * Utilities
     */

    /**
     * Checks whether file uses UTF-8 encoding
     *
     * See https://www.php.net/manual/de/function.mb-detect-encoding.php#91051
     *
     * @param string $file Path to file
     * @return bool
     */
    private static function isUTF8(string $file): bool
    {
        return substr(file_get_contents($file), 0, 3) == chr(0xEF) . chr(0xBB) . chr(0xBF);
    }
}
