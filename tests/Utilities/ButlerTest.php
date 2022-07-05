<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Pcbis\Tests\Utilities;

use Fundevogel\Pcbis\Utilities\Butler;

use org\bovigo\vfs\vfsStream;

class ButlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testLoadXML(): void
    {
        # Setup
        $xml = '<EINZELWERK>
            <Typknz ID="03">E</Typknz>
            <AutorSachtitel ID="06">Grammateion</AutorSachtitel>
            <Utitel ID="10">Griechische Lerngrammatik ab 8./9. Klasse bis incl. Universität</Utitel>
            <Mitarb ID="11">Mitarbeit:Lahmer, Karl</Mitarb>
            <Abb ID="13">1. Auflage 2018. 88 S. 26 cm</Abb>
            <SonstTxt ID="14">geheftet</SonstTxt>
            <Verlag ID="17">KLETT</Verlag>
            <Einband ID="20">GEH</Einband>
            <Ldpreis ID="21">14.95</Ldpreis>
            <Gewicht ID="27">198</Gewicht>
            <Mwstknz ID="33">1</Mwstknz>
            <Text1 ID="61">º15º&lt;span class="TextSchwarz"&gt;Weitere Informationen zu diesem Produkt finden Sie unter www.klett.de. &lt;/span&gt;</Text1>
            <Erschjahr ID="66">2018</Erschjahr>
            <KlassGruppe ID="91">842/000</KlassGruppe>
            <KlassGruppe ID="91">815/000</KlassGruppe>
            <KlassGruppe ID="91">835/000</KlassGruppe>
            <Sprachschl ID="94">01</Sprachschl>
            <Sprachschl ID="94">22</Sprachschl>
            <EAN ID="95">9783126632164</EAN>
            <Breite ID="F8">195</Breite>
            <Hoehe ID="F9">259</Hoehe>
            <Tiefe ID="FA">5</Tiefe>
        </EINZELWERK>';

        # Run function
        $result = Butler::loadXML($xml);

        # Assert result
        $this->assertEquals($result, [
            'Typknz' => 'E',
            'AutorSachtitel' => 'Grammateion',
            'Utitel' => 'Griechische Lerngrammatik ab 8./9. Klasse bis incl. Universität',
            'Mitarb' => 'Mitarbeit:Lahmer, Karl',
            'Abb' => '1. Auflage 2018. 88 S. 26 cm',
            'SonstTxt' => 'geheftet',
            'Verlag' => 'KLETT',
            'Einband' => 'GEH',
            'Ldpreis' => '14.95',
            'Gewicht' => '198',
            'Mwstknz' => '1',
            'Text1' => 'º15º&lt;span class="TextSchwarz"&gt;Weitere Informationen zu diesem Produkt finden Sie unter www.klett.de. &lt;/span&gt;',
            'Erschjahr' => '2018',
            'KlassGruppe' => [
                '842/000',
                '815/000',
                '835/000'
            ],
            'Sprachschl' => [
                '01',
                '22'
            ],
            'EAN' => '9783126632164',
            'Breite' => '195',
            'Hoehe' => '259',
            'Tiefe' => '5',
        ]);
    }


    public function testReverseName(): void
    {
        # Run function #1
        $result1 = Butler::reverseName('Doe, John');

        # Assert result #1
        $this->assertEquals($result1, 'John Doe');

        # Run function #2
        $result2 = Butler::reverseName('Doe# John', '#');

        # Assert result #2
        $this->assertEquals($result2, 'John Doe');
    }


    public function testDownloadCover(): void
    {
        # Setup
        # (1) Virtual directory
        $root = vfsStream::setup('home');

        # (2) Fixture file path
        $isbn = '978-3-314-10561-6';  # Die Tode meiner Mutter
        $fixture = sprintf('%s/../fixtures/%s.jpg', __DIR__, $isbn);

        # (3) Output file path
        $path = $root->url() . '/example.jpg';

        # Run function
        $result = Butler::downloadCover($isbn, $path);

        # Assert result
        if (class_exists('GuzzleHttp\Client')) {
            $this->assertTrue($result);
            $this->assertFileEquals($fixture, $path);
        } else {
            $this->assertFalse($result);
        }
    }
}
