<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Pdk\Base\HttpResponses;
use MyParcelNL\Sdk\src\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiResponse implements ApiResponseInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->statusCode = $response->getStatusCode();
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isErrorResponse(): bool
    {
        return $this->getStatusCode() < HttpResponses::HTTP_OK || $this->getStatusCode() >= 299;
    }

    /**
     * @return bool
     */
    public function isOkResponse(): bool
    {
        return ! $this->isErrorResponse();
    }

    /**
     * @return bool
     */
    public function isUnprocessableEntity(): bool
    {
        return HttpResponses::HTTP_UNPROCESSABLE_ENTITY === $this->getStatusCode();
    }

    /**
     * @param  string $body
     */
    protected function parseErrors(string $body): void
    {
        $this->errors = array_map(
            static function ($errorData) {
                return new ApiException($errorData['field'] . ': ' . $errorData['message']);
            },
            json_decode($body, true)['errors'] ?? []
        );
    }
}
