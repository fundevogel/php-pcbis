<?php

namespace PHPCBIS\Products\Audio\Types;

use PHPCBIS\Helpers\Butler;
use PHPCBIS\Products\Audio\Audio;
use PHPCBIS\Traits\DownloadCover;


/**
 * Class Audiobook
 *
 * KNV product category 'Hörbuch'
 *
 * @package PHPCBIS
 */

class Audiobook extends Audio {
    /**
     * Traits
     */

    use DownloadCover;


    /**
     * Constructor
     */

    public function __construct(array $source, array $props) {
        parent::__construct($source, $props);

        $this->isAudiobook = true;
    }
}
