<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Exception;

use Exception;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use Throwable;

class ApiException extends Exception
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @var string|null
     */
    private                                  $requestId;

    private readonly ClientResponseInterface $response;

    /**
     * @param  \Throwable|null $previous
     */
    public function __construct(ClientResponseInterface $response, int $code = 0, Throwable $previous = null)
    {
        $body = json_decode($response->getBody(), true);

        $this->response  = $response;
        $this->errors    = $body['errors'] ?? [];
        $this->requestId = $body['request_id'] ?? null;

        parent::__construct(
            sprintf(
                'Request failed. Status code: %s. Errors: %s',
                $response->getStatusCode(),
                $body['message']
            ),
            $code,
            $previous
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getResponse(): ClientResponseInterface
    {
        return $this->response;
    }
}
