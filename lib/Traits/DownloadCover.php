<?php

namespace Pcbis\Traits;

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
     * Path to downloaded cover images
     *
     * @var string
     */
    protected $imagePath = null;


    /**
     * User-Agent used when downloading cover images
     *
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0';


    /**
     * Setters & getters
     */

    public function setImagePath(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }


    public function getImagePath(): string
    {
        return $this->imagePath;
    }


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
        # Build path to file
        # (1) Directory
        if ($this->imagePath === null) {
            $this->imagePath = dirname(__DIR__, 2) . '/images';
        }

        # (2) Filename
        if ($fileName === null) {
            $fileName = $this->isbn;
        }

        # (3) Complete path
        $file = $this->imagePath . '/' . $fileName . '.jpg';

        # Skip if file exists & overwriting it is disabled
        if (file_exists($file) && !$overwrite) {
            return true;
        }

        # Otherwise, create directory if necessary
        if (!file_exists($this->imagePath)) {
            mkdir(dirname($file), 0755, true);
        }

        # Download cover image
        $success = false;

        if ($handle = fopen($file, 'w')) {
            $client = new GuzzleClient();
            $url = 'https://portal.dnb.de/opac/mvb/cover?isbn=' . $this->isbn;

            try {
                $response = $client->get($url, ['sink' => $handle]);
                $success = true;

            } catch (GuzzleException $e) {}
        }

        return $success;
    }
}
