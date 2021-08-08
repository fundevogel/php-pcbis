<?php

/**
 * Testing PHPCBIS - pcbis.de helper library
 *
 * @link https://github.com/Fundevogel/php-pcbis
 * @license GPL v3
 */

namespace Pcbis\Tests;

use Pcbis\Traits\DownloadCover;

use PHPUnit\Framework\TestCase;


class DownloadCoverTest extends TestCase
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
        self::$filePath = __DIR__ . '/../fixtures/DownloadCoverTest';

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

    public function testDownloadCover(): void
    {
        # Setup
        # (1) ISBNs
        $isbns = [
            '978-3-95854-151-1',  # Der Stein und das Meer
            '978-3-314-10561-6',  # Die Tode meiner Mutter
            '978-3-407-75554-4',  # Helsin Apelsin und der Spinner
            '978-3-95640-221-0',  # Die Hundebande in Paris
            '978-3-95640-223-4',  # Q-R-T: Nächste Stunde: Außerirdisch
        ];

        foreach ($isbns as $isbn) {
            # Setup
            # (1) Object
            $object = $this->getMockForTrait(DownloadCover::class);
            $object->setImagePath(self::$filePath);
            $object->isbn = $isbn;

            # (2) Expected file
            $expected = self::$filePath . '/' . $isbn . '.jpg';

            # (3) Actual file
            $actual = self::$filePath . '/test_' . $isbn . '.jpg';

            # Run function
            $result = $object->downloadCover('test_' . $isbn);

            # Assert result
            $this->assertIsBool($result);
            $this->assertFileExists($actual);

            if (file_exists($actual)) {
                $this->assertFileEquals($expected, $actual);
            }
        }
    }


    public function testUserAgent(): void
    {
        # Setup
        # (1) Object
        $object = $this->getMockForTrait(DownloadCover::class);

        # (2) Expected user agent
        $expected = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';

        # (3) Actual file
        $changed = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.86 Safari/537.36';

        # Run function
        $result = $object->getUserAgent();

        # Assert result
        $this->assertEquals($expected, $result);

        # Run function
        $result = $object->setUserAgent($changed);

        # Assert result
        $this->assertEquals($changed, $result);
    }


    /**
     * Utilities
     */

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
