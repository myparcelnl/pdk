<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Account\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static ApiResponseInterface  doRequest(RequestInterface $request, string $responseClass)
 * @method static string  getBaseUrl()
 * @implements \MyParcelNL\Pdk\Api\Service\ApiServiceInterface
 */
class Api extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'api';
    }
}
