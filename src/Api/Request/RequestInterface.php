<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Request;

interface RequestInterface
{
    /**
     * Get the body of the request.
     */
    public function getBody(): ?string;

    /**
     * The headers of a request.
     */
    public function getHeaders(): array;

    /**
     * The HTTP method of the request.
     */
    public function getMethod(): string;

    /**
     * The path of the request, which will be used to build the URL. Parameters should be resolved.
     */
    public function getPath(): string;

    /**
     * The property that will be used in POST data to the API. Will also be used when parsing the response body if
     * there is no response property set.
     * @exampe { "data": { "<property>": [] } }
     */
    public function getProperty(): ?string;

    /**
     * Formatted query string.
     */
    public function getQueryString(): string;

    /**
     * The property that will be used in the response. Defaults to "property" if not set.
     *
     * @example { "data": { "<property>": [] } }
     */
    public function getResponseProperty(): ?string;
}
