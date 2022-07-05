<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Utilities;

use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Dir;
use Fundevogel\Pcbis\Helpers\Str;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Butler
 *
 * This class contains useful helper functions - pretty much like a butler
 */
class Butler
{
    /**
     * Reverses name, going from 'Doe, John' to 'John Doe'
     *
     * @param string $string Name to be reversed
     * @return string
     */
    public static function reverseName(string $string, string $delimiter = ','): string
    {
        $array = Str::split($string, $delimiter);
        $arrayReverse = array_reverse($array);

        return A::join($arrayReverse, ' ');
    }


    /**
     * Converts millimeters to centimeters
     *
     * @param string $string Millimeter information
     */
    public static function convertMM(string $string): array|string
    {
        # TODO: Messing up some other values, needs fixing
        # Edge case: string already contains width/height in centimeters
        # See 978-3-7891-2946-9
        if (Str::contains($string, ',')) {
            return $string;
        }

        return Str::replace($string / 10, '.', ',');
    }


    /**
     * Downloads cover images from the German National Library (DNB)
     *
     * @param string $isbn A given product's EAN/ISBN
     * @param mixed $file Path to download file OR file-like object
     * @param string $ua User-Agent used when downloading cover images
     * @return bool Download status
     */
    public static function downloadCover(string $isbn, mixed $file = null, ?string $ua = null): bool
    {
        # If not specified ..
        if (is_null($file)) {
            # .. provide fallback
            $file = sprintf('%s/%s.jpg', __DIR__, $isbn);
        }

        # If string was passed ..
        if (is_string($file)) {
            # .. create directory (if needed)
            Dir::make(dirname($file));
        }

        # Attempt to ..
        try {
            # .. download cover image
            $response = (new Client())->get(sprintf('https://portal.dnb.de/opac/mvb/cover?isbn=%s', $isbn), [
                'headers' => ['User-Agent' => $ua ?? 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0'],
                'sink' => $file,
            ]);

            # .. report back
            return true;
            # .. otherwise ..
        } catch (ClientException $e) {
        }

        # .. report failure
        return false;
    }


    /**
     *
     */
    public static function pluck(array $array): array
    {
        $output = [];

        foreach ($array as $item) {
            $output[$item['feldName']] = count($item['werte']) > 1
                ? $item['werte']
                : $item['werte'][0]
            ;
        }

        return $output;
    }
}
