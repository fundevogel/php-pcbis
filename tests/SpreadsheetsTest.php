<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests;

use Pcbis\Spreadsheets;

use PHPUnit\Framework\TestCase;


class SpreadsheetsTest extends TestCase
{
    /**
     * Properties
     */

    /**
     * Path to specific fixtures
     *
     * @var string
     */
    private static $filePath;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Define fixture directory
        self::$filePath = __DIR__ . '/fixtures/SpreadsheetsTest';

        # Clean up leftovers from prior tests
        self::cleanUp();
    }


    public static function tearDownAfterClass(): void
    {
        self::cleanUp();
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
        $file = self::$filePath . '/csv2array.csv';

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
        $file = self::$filePath . '/csv2array.csv';

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

        # (2) File path
        $file = self::$filePath . '/test_array2csv.csv';

        # Run function
        $result = Spreadsheets::array2csv($array, $file);

        # Assert result
        $this->assertTrue($result);
        $this->assertFileExists($file);
        $this->assertEquals(self::detectEncoding($file), 'utf-8');
    }


    /**
     * Utilities
     */

    /**
     * Detects character encoding of a file
     *
     * @param string $file Path to file
     * @return string Character encoding
     */
    private static function detectEncoding(string $file): string
    {
        $output = []; exec('file -i ' . $file, $output);

        if (isset($output[0])) {
            $charset = explode('charset=', $output[0]);

            if (isset($charset[1])) return $charset[1];
        }

        return '';
    }


    /**
     * Deletes generated files in test directory
     *
     * @return void
     */
    private static function cleanUp(): void
    {
        array_map('unlink', glob(self::$filePath . '/test_*.{csv,jpg}', GLOB_BRACE));
    }
}
