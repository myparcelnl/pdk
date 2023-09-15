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
     * @param  class-string<ApiResponseInterface> $responseClass
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass = ApiResponse::class
    ): ApiResponseInterface;
}
