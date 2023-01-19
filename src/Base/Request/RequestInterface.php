<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Request;

interface RequestInterface
{
    /**
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getProperty(): string;

    /**
     * @return string
     */
    public function getQueryString(): string;
}
