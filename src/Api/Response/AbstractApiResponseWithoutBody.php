<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use Error;
use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Pdk\Base\HttpResponses;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiResponseWithoutBody implements ApiResponseInterface
{
    /**
     * @var string
     */
    private $body;

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
        $this->body       = (string) $response->getBody();

        if ($this->isUnprocessableEntity()) {
            $this->parseErrors($this->body);
        }
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
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
        return $this->statusCode < HttpResponses::HTTP_OK || $this->statusCode >= 299;
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
     * @return bool
     */
    public function resourceNotFound(): bool
    {
        return HttpResponses::HTTP_NOT_FOUND === $this->statusCode;
    }

    /**
     * @param  string $body
     */
    protected function parseErrors(string $body): void
    {
        $this->errors = array_map(
            static function ($errorData) {
                return new Error($errorData['field'], $errorData['message']);
            },
            json_decode($body, true)['errors'] ?? []
        );
    }
}
