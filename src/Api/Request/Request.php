<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Request;

use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * @property string|null           $body
 * @property array{string, string} $headers
 * @property string                $method
 * @property array{string, string} $parameters
 * @property string                $path
 * @property string                $property
 * @property string|null           $responseProperty
 */
class Request implements RequestInterface
{
    /**
     * @var null|string
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $method = HttpRequest::METHOD_GET;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    private $property;

    /**
     * @var null|string
     */
    private $responseProperty;

    /**
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $this->body             = $config['body'] ?? $this->body;
        $this->headers          = $config['headers'] ?? $this->headers;
        $this->method           = $config['method'] ?? $this->method;
        $this->parameters       = $config['parameters'] ?? $this->parameters;
        $this->path             = $config['path'] ?? $this->path;
        $this->property         = $config['property'] ?? $this->property;
        $this->responseProperty = $config['responseProperty'] ?? $this->responseProperty;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return http_build_query($this->getParameters());
    }

    /**
     * @return null|string
     */
    public function getResponseProperty(): ?string
    {
        return $this->responseProperty;
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return sprintf('%s?%s:%s', $this->getPath(), $this->getQueryString(), http_build_query($this->getHeaders()));
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        return array_filter($this->parameters);
    }
}
