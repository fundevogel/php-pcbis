<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product\Types;

use Fundevogel\Pcbis\Classes\Product\Types\Book;

class BookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Book(['EAN' => 'xxx']);

        # Assert result
        $this->assertTrue($obj->isBook());
    }


    public function testISBN(): void
    {
        # Setup
        $isbn = '978-3-314-10561-6';  # Die Tode meiner Mutter

        # Run function
        $obj = new Book(['EAN' => $isbn]);

        # Assert result
        $this->assertEquals($obj->isbn(), $isbn);
    }


    public function testBinding(): void
    {
        # Run function
        $obj = new Book(['EAN' => 'xxx', 'Einband' => 'GEB']);

        # Assert result
        $this->assertEquals($obj->binding(), 'gebunden');
    }


    public function testPageCount(): void
    {
        # Run function
        $obj = new Book(['EAN' => 'xxx', 'Abb' => '1. Auflage 2021. 48 S. durchgehend farbig illustriert 28 cm']);

        # Assert result
        $this->assertEquals($obj->pageCount(), '48');
    }


    public function testAntolin(): void
    {
        # Run function
        $obj = new Book(['EAN' => 'xxx', 'IndexSchlagw' => ['Antolin (3. Klasse)']]);

        # Assert result
        $this->assertEquals($obj->antolin(), '3. Klasse');
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Book([
            'EAN' => 'xxx',
            'Einband' => 'GEB',
            'Abb' => '1. Auflage 2021. 48 S. durchgehend farbig illustriert 28 cm',
            'IndexSchlagw' => ['Antolin (3. Klasse)'],
        ]);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
