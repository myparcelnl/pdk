<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Request;

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
        return sprintf('%s?%s:%s', $this->getPath(), $this->getQueryString(), http_build_query($this->getHeaders()));
    }

    /**
     * @return array
     */
    protected function getQueryParameters(): array
    {
        return [];
    }
}
