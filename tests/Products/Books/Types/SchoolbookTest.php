<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products\Books\Types;

use Fundevogel\Pcbis\Webservice;
use Fundevogel\Pcbis\Products\Books\Types\Schoolbook;

class SchoolbookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testType(): void
    {
        # Run function
        $obj = new Schoolbook(['EAN' => 'xxx'], new Webservice());

        # Assert result
        $this->assertTrue($obj->isBook());
        $this->assertTrue($obj->isSchoolbook());
    }


    public function testSubject(): void
    {
        # Setup
        $data = [
            'EAN' => '978-3-12-663216-4',
            'Utitel' => 'Griechische Lerngrammatik ab 8./9. Klasse bis incl. Universität',
            'AutorSachtitel' => 'Grammateion',
            'Abb' => '1. Auflage 2018. 88 S. 26 cm',
            'Kurztitel' => 'Grammateion',
            'IndexStichw' => [
                'Grammateion                             ',
                'Griechische                             ',
                'Lerngrammatik                           ',
                'ab                                      ',
                '8                                       ',
                '9                                       ',
                'Klasse                                  ',
                'bis                                     ',
                'incl                                    ',
                'Universität                             '
            ],
            'IndexSchlagw' => [
                'Altgriechisch; Grammatik',
                'Altgriechisch; Schulbuch (Gymnasium)',
            ],
        ];

        # Run function
        $obj = new Schoolbook($data, new Webservice());

        # Assert result
        $this->assertEquals($obj->subject(), 'Altgriechisch');
    }
}
