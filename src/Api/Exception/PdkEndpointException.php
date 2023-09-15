<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Exception;

use Exception;
use Throwable;

class PdkEndpointException extends Exception
{
    private int $statusCode;

    /**
     * @param  \Throwable|null $previous
     */
    public function __construct(string $message, int $statusCode = 0, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->setStatusCode($statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }
}
