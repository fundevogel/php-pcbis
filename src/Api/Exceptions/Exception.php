<?php

declare(strict_types=1);

/**
 * Simple PHP wrapper for pcbis.de API
 *
 * @link https://codeberg.org/Fundevogel/php-pcbis
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 */

namespace Fundevogel\Pcbis\Api\Exceptions;

use Fundevogel\Pcbis\Interfaces\KnvException;

/**
 * Class Exception
 *
 * Custom base exception
 */
class Exception extends \Exception implements KnvException
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param string $description Detailed exception description
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
     * Exports HTTP status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->data->httpStatus ?? $this->data->status;
    }


    /**
     * Exports detailed exception description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
