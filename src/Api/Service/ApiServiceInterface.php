<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Pdk\Base\Request\RequestInterface;

interface ApiServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Base\Request\RequestInterface $request
     * @param  string                                        $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Concern\ApiResponseInterface
     */
    public function doRequest(RequestInterface $request, string $responseClass): ApiResponseInterface;

    /**
     * @return string
     */
    public function getBaseUrl(): string;
}
