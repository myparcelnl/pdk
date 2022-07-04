<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

interface RequestInterface
{
    /**
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * @return string
     */
    public function getHttpMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getQueryString(): string;
}
