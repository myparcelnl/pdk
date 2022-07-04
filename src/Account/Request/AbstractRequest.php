<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var string|null
     */
    protected $body;

    /**
     * @var string
     */
    protected $path;

    /**
     * @return string
     */
    abstract public function getHttpMethod(): string;

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
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
        return http_build_query($this->getQueryParameters());
    }

    /**
     * @return array
     */
    protected function getQueryParameters(): array
    {
        return [];
    }
}
