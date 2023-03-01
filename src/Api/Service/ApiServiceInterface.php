<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Api\Response\ApiResponseInterface;
use MyParcelNL\Pdk\Base\Request\RequestInterface;

/**
 * Used to make requests to an API.
 */
interface ApiServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Base\Request\RequestInterface $request
     * @param  string                                        $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Response\ApiResponseInterface
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass = ApiResponse::class
    ): ApiResponseInterface;
}
