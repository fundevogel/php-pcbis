<?php

declare(strict_types=1);

/**
 * Testing php-pcbis - simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Tests\Classes\Product;

use Fundevogel\Pcbis\Classes\Product\Product;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testCast2String(): void
    {
        # Run function
        $result = new Product(['EAN' => 'xxx', 'AutorSachtitel' => 'Doe, John', 'Titel' => 'Title']);

        # Assert result
        $this->assertEquals($result->__toString(), 'John Doe: Title');
    }


    public function testTitle(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Titel' => 'Title']);

        # Assert result
        $this->assertEquals($obj->title(), 'Title');
    }


    public function testSubtitle(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx']);

        # Assert result #1
        $this->assertEquals($obj->subtitle(), '');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'Utitel' => 'Subtitle']);

        # Assert result #2
        $this->assertEquals($obj->subtitle(), 'Subtitle');
    }


    public function testPublisher(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'IndexVerlag' => 'Verlag']);

        # Assert result #1
        $this->assertEquals($obj->publisher(), 'Verlag');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'IndexVerlag' => ['Verlag  ', '  Verlagsgruppe']]);

        # Assert result #2
        $this->assertEquals($obj->publisher()->value(), ['Verlag', 'Verlagsgruppe']);
    }


    public function testDescription(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Text1' => '06<span class="TextSchwarz">Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen. </span>15<span class="TextSchwarz">Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen. </span>01<span class="TextSchwarz">Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.</span>']);

        # Assert result
        $this->assertEquals(
            $obj->description(),
            ''
            . 'Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen.' . '<br \>'
            . 'Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen.' . '<br \>'
            . 'Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.'
        );
        $this->assertEquals($obj->description()->value(), [
            'Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen.',
            'Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen.',
            'Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.',
        ]);
    }


    public function testRetailPrice(): void
    {
        foreach (['15', '15.00'] as $retailPrice) {
            # Run function
            $obj = new Product(['EAN' => 'xxx', 'PreisEurD' => $retailPrice]);

            # Assert result
            $this->assertEquals($obj->retailPrice(), '15,00');
        }
    }


    public function testReleaseYear(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Erschjahr' => '2018']);

        # Assert result
        $this->assertEquals($obj->releaseYear(), '2018');
    }


    public function testAge(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'Alter' => '12']);

        # Assert result #1
        $this->assertEquals($obj->age(), 'ab 12 Jahren');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'Alter' => '06']);

        # Assert result #2
        $this->assertEquals($obj->age(), 'ab 6 Jahren');
    }


    public function testSeries(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter']);

        # Assert result #1
        $this->assertEquals($obj->series()->value(), ['Harry Potter' => null]);

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter', 'BandnrVerwieseneReihe1' => '3: Der Gefangene von Askaban']);

        # Assert result #2
        $this->assertEquals($obj->series()->value(), ['Harry Potter' => '3: Der Gefangene von Askaban']);
    }


    public function testIsSeries(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx']);

        # Assert result #1
        $this->assertFalse($obj->isSeries());

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'VerwieseneReihe1' => 'Harry Potter']);

        # Assert result #2
        $this->assertTrue($obj->isSeries());
    }


    public function testWeight(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Gewicht' => '80']);

        # Assert result
        $this->assertEquals($obj->weight(), '80');
    }


    public function testWidth(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Breite' => '80']);

        # Assert result
        $this->assertEquals($obj->width(), '8');
    }


    public function testHeight(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Höhe' => '80']);

        # Assert result
        $this->assertEquals($obj->height(), '8');
    }


    public function testDepth(): void
    {
        # Run function
        $obj = new Product(['EAN' => 'xxx', 'Tiefe' => '80']);

        # Assert result
        $this->assertEquals($obj->depth(), '8');
    }


    public function testDimensions(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'Breite' => '80']);

        # Assert result #1
        $this->assertEquals($obj->dimensions(), '8');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'Höhe' => '80']);

        # Assert result #2
        $this->assertEquals($obj->dimensions(), '8');

        # Run function #3
        $obj = new Product(['EAN' => 'xxx', 'Breite' => '80', 'Höhe' => '50']);

        # Assert result #3
        $this->assertEquals($obj->dimensions(), '8x5');

        # Run function #4
        $obj = new Product(['EAN' => 'xxx', 'Breite' => '80', 'Höhe' => '50', 'Tiefe' => '25']);

        # Assert result #4
        $this->assertEquals($obj->dimensions(), '8x5x2,5');
    }


    public function testLanguages(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'Sprachschl' => '02']);

        # Assert result #1
        $this->assertEquals($obj->languages(), 'Englisch');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'Sprachschl' => ['01', '02']]);

        # Assert result #2
        $this->assertEquals($obj->languages()->value(), ['Deutsch', 'Englisch']);
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
        $obj = new Product(['EAN' => 'xxx']);

        # Assert result #1
        $this->assertEquals($obj->vat(), '');

        foreach ($vatCodes as $vatCode => $expected) {
            # Run function #2
            $obj = new Product(['EAN' => 'xxx', 'Mwstknz' => $vatCode]);

            # Assert result #2
            $this->assertEquals($obj->vat(), $expected);
        }
    }


    public function testCategories(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'IndexSchlagw' => 'Antolin 3. KLASSE']);

        # Assert result #1
        $this->assertEquals($obj->categories(), '');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'IndexSchlagw' => ['Papp-Bilderbuch', 'Vulkane']]);

        # Assert result #2
        $this->assertEquals($obj->categories()->value(), ['Bilderbuch']);
    }


    public function testTopics(): void
    {
        # Run function #1
        $obj = new Product(['EAN' => 'xxx', 'IndexSchlagw' => 'Antolin 3. KLASSE']);

        # Assert result #1
        $this->assertEquals($obj->topics(), '');

        # Run function #2
        $obj = new Product(['EAN' => 'xxx', 'IndexSchlagw' => ['Papp-Bilderbuch', 'Vulkane']]);

        // # Assert result #2
        $this->assertEquals($obj->topics()->value(), ['Vulkane']);
    }


    public function testExport(): void
    {
        # Run function
        $obj = new Product([
            'EAN' => 'xxx',
            'AutorSachtitel' => 'Doe, John',
            'Titel' => 'Title',
            'Utitel' => 'Subtitle',
            'IndexVerlag' => 'Verlag',
            'Text1' => '06<span class="TextSchwarz">Mama kann viele Dinge sein. Manchmal ist sie laut, manchmal ist sie leise. Meistens ist sie ziemlich normal. Nur am Abend steht sie auf der Opernbühne und stirbt tausend dramatische Tode. Und das Publikum ist hingerissen. </span>15<span class="TextSchwarz">Im Laufe eines einzigen Tages macht Mama so manche Verwandlung durch: Mal ist sie ganz still, mal laut und aufbrausend. Am Tag spielt sie mit den Kindern. Am Abend aber verwandelt sich Mama in eine glamouröse Opernsängerin. In einer ihrer vielen Rollen steht sie auf der Bühne und be geistert das Publikum. Besonderen Eindruck hinterlassen ihre unzähligen tragischen, aber auch ulkigen Sterbeszenen. Von Langeweile keine Spur! Zu ihrem Bilderbuch-Debüt wurde Carla Haslbauer von der Welt der Oper inspiriert. Ihr leichter und farbenfroher Stil zeugt von ihrer Liebe zum Comic. Mit viel Humor teilt sie eine universelle Erkenntnis: dass die Eltern nicht nur Eltern sind, sondern in viele verschiedene Rollen schlüpfen. </span>01<span class="TextSchwarz">Carla Haslbauer wurde in Frankfurt am Main geboren und wuchs in der Kleinstadt Bad Nauheim auf. Seit dem Abschluss in Illustration Fiction an der Hoch schule Luzern Design & Kunst arbeitet sie als freischaffende Illustratorin. Als Mitglied des Comic-Kollektivs Corner Collective realisiert sie regel mäßig auch Comic-Projekte. Ihre Inspiration findet sie in der Natur und dem Alltagsgeschehen um sie herum. Gerne gräbt sie auch in ihren Kindheitserinnerungen und findet so manche erzählenswerte Geschichte. »Die Tode meiner Mutter« ist ihr erstes Bilderbuch.</span>',
            'PreisEurD' => '15.00',
            'Erschjahr' => '2018',
            'Alter' => '06',
            'VerwieseneReihe1' => 'Harry Potter', 'BandnrVerwieseneReihe1' => '3: Der Gefangene von Askaban',
            'Gewicht' => '80',
            'Breite' => '80',
            'Höhe' => '80',
            'Sprachschl' => '02',
        ]);

        # Assert result
        $this->assertIsArray($obj->export());
    }
}
