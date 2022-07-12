<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @return string
     */
    abstract public function getHttpMethod(): string;

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return null;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return http_build_query($this->getQueryParameters());
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return "{$this->getPath()}?{$this->getQueryString()}";
    }

    /**
     * @return array
     */
    protected function getQueryParameters(): array
    {
        return [];
    }
}
