<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Account\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;

interface ApiServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Account\Request\RequestInterface $request
     * @param  string                                           $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Concern\ApiResponseInterface
     */
    public function doRequest(RequestInterface $request, string $responseClass): ApiResponseInterface;
}
