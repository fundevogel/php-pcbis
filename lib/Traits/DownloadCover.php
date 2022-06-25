<?php

namespace Pcbis\Traits;

use Pcbis\Helpers\Butler;


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
    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
    }


    public function getImagePath(): string
    {
        return $this->imagePath;
    }


    public function setUserAgent(string $userAgent): void
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
    public function downloadCover(?string $fileName = null, bool $overwrite = false): bool
    {
        # Determine ..
        # (1).. directory
        $directory = $this->imagePath ?? sprintf('%s/images', dirname(__DIR__, 2));

        # (2) .. filename
        $fileName = $fileName ?? $this->isbn;

        return Butler::downloadCover($this->isbn, $fileName, $directory, $overwrite);
    }
}
