<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use BadMethodCallException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class ExampleJsonResponse extends Response
{
    /**
     * @var null|array
     */
    protected $responseContent;

    /**
     * @param  null|array  $responseContent
     * @param  null        $body
     * @param  string|null $reason
     */
    public function __construct(
        ?array $responseContent = null,
        int    $status = 200,
        array  $headers = [],
               $body = null,
        string $version = '1.1',
        string $reason = null
    ) {
        parent::__construct($status, $headers, $body, $version, $reason);
        $this->setResponseContent($responseContent);
    }

    public function getBody(): StreamInterface
    {
        $content = $this->getContent();
        $body    = null;

        if (! empty($content)) {
            $body = json_encode($content, JSON_THROW_ON_ERROR);
        }

        return Utils::streamFor($body);
    }

    public function getContent(): array
    {
        return [
            'data' => [
                $this->getResponseProperty() => $this->responseContent ?? $this->getDefaultResponseContent(),
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    public function getStatusCode(): int
    {
        return \Symfony\Component\HttpFoundation\Response::HTTP_OK;
    }

    /**
     * @param  null|array $responseContent
     */
    public function setResponseContent(?array $responseContent): self
    {
        $this->responseContent = $responseContent;

        return $this;
    }

    protected function getDefaultResponseContent(): array
    {
        return $this->responseContent ?? [];
    }

    protected function getResponseProperty(): string
    {
        throw new BadMethodCallException('This method should be overridden when not overriding getContent()');
    }
}
