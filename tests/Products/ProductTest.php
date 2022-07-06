<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Products;

use Fundevogel\Pcbis\Products\Product;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function setUp(): void
    {
        # Start output buffer
        ob_start();
    }


    public function testCast2String(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'AutorSachtitel' => 'Doe, John', 'Titel' => 'Title'];

        # Run function
        echo $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals(ob_get_contents(), 'John Doe: Title');
    }


    public function tearDown(): void
    {
        # Clear output buffer
        ob_end_clean();
    }


    public function testEAN(): void
    {
        # Setup
        $ean = '978-3-314-10561-6';  # Die Tode meiner Mutter

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [['EAN' => $ean]]);

        # Assert result
        $this->assertEquals($obj->ean(), $ean);
    }


    public function testTitle(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Titel' => 'Title'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->title(), 'Title');
    }


    public function testSubtitle(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Utitel' => 'Subtitle'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->subtitle(), 'Subtitle');
    }


    public function testPublisher(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx', 'IndexVerlag' => 'Verlag'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertEquals($obj->publisher(), 'Verlag');

        # Setup #2
        $args = ['EAN' => 'xxx', 'IndexVerlag' => ['Verlag  ', '  Verlagsgruppe']];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertEquals($obj->publisher(), ['Verlag', 'Verlagsgruppe']);
    }


    public function testDescription(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Text1' => '06<span class="TextSchwarz">Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen. </span>15<span class="TextSchwarz">Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen. </span>01<span class="TextSchwarz">Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.</span>'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->description(), [
            'Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen.',
            'Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen.',
            'Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.',
        ]);
    }


    public function testRetailPrice(): void
    {
        # Setup
        foreach (['15', '15.00'] as $retailPrice) {
            # Run function
            $obj = $this->getMockForAbstractClass(Product::class, [['EAN' => 'xxx', 'PreisEurD' => $retailPrice]]);

            # Assert result
            $this->assertEquals($obj->retailPrice(), '15,00');
        }
    }


    public function testReleaseYear(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Erschjahr' => '2018'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->releaseYear(), '2018');
    }


    public function testAge(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx', 'Alter' => '12'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertEquals($obj->age(), 'ab 12 Jahren');

        # Setup #2
        $args = ['EAN' => 'xxx', 'Alter' => '06'];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertEquals($obj->age(), 'ab 6 Jahren');
    }


    public function testSeries(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertEquals($obj->series(), ['Harry Potter' => '']);

        # Setup #2
        $args = ['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter', 'BandnrVerwieseneReihe1' => '3: Der Gefangene von Askaban'];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertEquals($obj->series(), ['Harry Potter' => '3: Der Gefangene von Askaban']);
    }


    public function testIsSeries(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertFalse($obj->isSeries());

        # Setup #2
        $args = ['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter'];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertTrue($obj->isSeries());
    }


    public function testWeight(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Gewicht' => '80'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->weight(), '80');
    }


    public function testWidth(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Breite' => '80'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->width(), '8');
    }


    public function testHeight(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Höhe' => '80'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->height(), '8');
    }


    public function testDepth(): void
    {
        # Setup
        $args = ['EAN' => 'xxx', 'Tiefe' => '80'];

        # Run function
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result
        $this->assertEquals($obj->depth(), '8');
    }


    public function testDimensions(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx', 'Breite' => '80'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertEquals($obj->dimensions(), '8');

        # Setup #2
        $args = ['EAN' => 'xxx', 'Höhe' => '80'];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertEquals($obj->dimensions(), '8');

        # Setup #3
        $args = ['EAN' => 'xxx', 'Breite' => '80', 'Höhe' => '50'];

        # Run function #3
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #3
        $this->assertEquals($obj->dimensions(), '8x5');

        # Setup #4
        $args = ['EAN' => 'xxx', 'Breite' => '80', 'Höhe' => '50', 'Tiefe' => '25'];

        # Run function #4
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #4
        $this->assertEquals($obj->dimensions(), '8x5x2,5');
    }


    public function testLanguages(): void
    {
        # Setup #1
        $args = ['EAN' => 'xxx', 'Sprachschl' => '02'];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #1
        $this->assertEquals($obj->languages(), 'Englisch');

        # Setup #2
        $args = ['EAN' => 'xxx', 'Sprachschl' => ['01', '02']];

        # Run function #2
        $obj = $this->getMockForAbstractClass(Product::class, [$args]);

        # Assert result #2
        $this->assertEquals($obj->languages(), ['Deutsch', 'Englisch']);
    }


    public function testVAT(): void
    {
        # Setup #1
        $vatCodes = [
            '0' => 'kein',
            '1' => 'halb',
            '2' => 'voll',
        ];

        # Run function #1
        $obj = $this->getMockForAbstractClass(Product::class, [['EAN' => 'xxx']]);

        # Assert result #1
        $this->assertEquals($obj->vat(), '');

        foreach ($vatCodes as $vatCode => $expected) {
            # Setup #2
            $args = ['EAN' => 'xxx', 'Mwstknz' => $vatCode];

            # Run function #2
            $obj = $this->getMockForAbstractClass(Product::class, [$args]);

            # Assert result #2
            $this->assertEquals($obj->vat(), $expected);
        }
    }
}
