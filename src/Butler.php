<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Helpers\A;
use Fundevogel\Pcbis\Helpers\Dir;
use Fundevogel\Pcbis\Helpers\Str;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Butler
 *
 * This class contains useful helper functions - pretty much like a butler
 */
class Butler
{
    /**
     * Converts XML to PHP array
     *
     * @param string $data Response object from KNV's API
     * @return array
     */
    public static function loadXML(string $data): array
    {
        # Prepare raw XML response to be loaded by SimpleXML
        $data = Str::replace($data, '&', '&amp;');

        # Convert XML to JSON to PHP array
        $xml = simplexml_load_string($data);
        $json = json_encode($xml);

        return json_decode($json, true);
    }


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
     *
     * @return string
     */
    public static function convertMM(string $string): string
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
     * @param string $fileName Filename for the image to be downloaded
     * @param string $directory Target download directory
     * @param bool $overwrite Whether existing file should be overwritten
     * @param string $ua User-Agent used when downloading cover images
     * @return bool Download status
     */
    public static function downloadCover(
        string $isbn,
        ?string $fileName = null,
        ?string $directory = null,
        bool $overwrite = false,
        ?string $ua = null
    ): bool {
        # Build path to file
        $file = sprintf('%s/%s.jpg', $directory ?? __DIR__, $fileName ?? $isbn);

        # Skip if file exists & overwriting it is disabled
        if (file_exists($file) && !$overwrite) {
            return true;
        }

        # Create directory (if needed)
        Dir::make(dirname($file));

        # Download cover image
        $success = false;

        if ($handle = fopen($file, 'w')) {
            # Determine image URL
            $url = sprintf('https://portal.dnb.de/opac/mvb/cover?isbn=%s', $isbn);

            try {
                # Initialize client
                $client = new GuzzleClient();

                # Start download
                $response = $client->get($url, [
                    'headers' => ['User-Agent' => $ua ?? 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0'],
                    'sink' => $handle,
                ]);

                # Report back
                $success = true;
            } catch (ClientException $e) {
            }
        }

        return $success;
    }
}
