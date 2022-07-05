<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 3.0.0
 */

namespace Fundevogel\Pcbis;

use Fundevogel\Pcbis\Api\Webservice;

/**
 * Class Pcbis
 *
 * Base class for everything pcbis.de
 */
class Pcbis
{
    /**
     * Properties
     */

    /**
     * Webservice API client
     *
     * @var \Fundevogel\Pcbis\Api\Webservice
     */
    public Webservice $api;


    /**
     * Constructor
     *
     * @param array $credentials Login credentials
     * @param string $cache Cache object
     * @return void
     */
    public function __construct(?array $credentials = null, public mixed $cache = null)
    {
        $this->api = Webservice($credentials);
    }
}
