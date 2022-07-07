<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api\Exceptions;

/**
 * Class KNVException
 *
 * Custom base exception
 */
class KNVException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param int $description Detailed exception description
     * @param \stdClass $data Response body as JSON object
     * @param \Throwable $previous Previous exception (if nested)
     * @return void
     */
    public function __construct(string $message, int $code, public string $description = '', public \stdClass $data, ?\Throwable $previous = null)
    {
        # Make sure everything 'just works'
        parent::__construct($message, $code, $previous);
    }


    /**
     * Exports exception code & message when casting to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf("%s [%s]: %s\n", __CLASS__, $this->code, $this->message);
    }


    /**
     * Retrieves HTTP status
     *
     * @return string
     */
    public function getStatus(): string
    {
        if (array_key_exists('httpStatus', $this->data)) {
            return $this->data['httpStatus'];
        }

        return $this->data['status'];
    }
}
