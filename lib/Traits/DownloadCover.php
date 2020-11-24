<?php

namespace PHPCBIS\Traits;

use PHPCBIS\Helpers\Butler;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleException;


/**
 * Trait DownloadCover
 *
 * Provides ability to download cover images from the German National Library
 *
 * @package PHPCBIS
 */

trait DownloadCover
{
    /**
     * Properties
     */

    /**
     * User-Agent used when downloading cover images
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    /**
     * Setters & getters
     */

    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }


    /**
     * Methods
     */

    /**
     * Downloads cover images from the German National Library
     *
     * @param string $fileName - Filename for the image to be downloaded
     * @param bool $overwrite - Whether existing file should be overwritten
     * @return bool
     */
    public function downloadCover(string $fileName = null, bool $overwrite = false): bool
    {
        if ($fileName == null) {
            $fileName = $this->isbn;
        }

        $file = realpath($this->imagePath . '/' . $fileName . '.jpg');

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        if (file_exists($file) && !$overwrite) {
            return true;
        }

        $success = false;

        if ($handle = fopen($file, 'w')) {
            $client = new GuzzleClient();
            $url = 'https://portal.dnb.de/opac/mvb/cover.htm?isbn=' . $this->isbn;

            try {
                $response = $client->get($url, ['sink' => $handle]);
                $success = true;
            } catch (GuzzleException $e) {}
        }

        return $success;
    }
}
