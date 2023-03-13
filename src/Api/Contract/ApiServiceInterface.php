<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Contract;

use MyParcelNL\Pdk\Api\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Response\ApiResponse;

/**
 * Used to make requests to an API.
 */
interface ApiServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
     * @param  string                                       $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ApiResponseInterface
     */
    public function doRequest(
        RequestInterface $request,
        string                                       $responseClass = ApiResponse::class
    ): ApiResponseInterface;
}
