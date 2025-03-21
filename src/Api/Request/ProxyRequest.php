<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Request;

/**
 * Request object for proxying requests to the Addresses microservice
 */
class ProxyRequest implements RequestInterface
{
    /**
     * @var string
     */
    private $method;
    
    /**
     * @var string
     */
    private $path;
    
    /**
     * @var string|null
     */
    private $body;
    
    /**
     * @var array
     */
    private $headers;
    
    /**
     * @var array
     */
    private $queryParams;
    
    /**
     * @param string      $method
     * @param string      $path
     * @param string|null $body
     * @param array       $queryParams
     * @param array       $headers
     */
    public function __construct(
        string $method,
        string $path,
        ?string $body = null,
        array $queryParams = [],
        array $headers = []
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->body = $body;
        $this->queryParams = $queryParams;
        $this->headers = $headers;
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
     * @return string|null
     */
    public function getProperty(): ?string
    {
        return null;
    }
    
    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return http_build_query($this->queryParams);
    }
    
    /**
     * @return string|null
     */
    public function getResponseProperty(): ?string
    {
        return null;
    }
} 