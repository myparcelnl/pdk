<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Request;

use Symfony\Component\HttpFoundation\Request as HttpRequest;

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
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $this->body       = $config['body'] ?? $this->body;
        $this->headers    = $config['headers'] ?? $this->headers;
        $this->method     = $config['method'] ?? $this->method;
        $this->parameters = $config['parameters'] ?? $this->parameters;
        $this->path       = $config['path'] ?? $this->path;
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
    public function getQueryString(): string
    {
        return http_build_query($this->getParameters());
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
