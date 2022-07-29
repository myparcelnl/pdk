<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Facade;

use GuzzleHttp\Handler\MockHandler;
use MyParcelNL\Pdk\Account\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Pdk\Facade\Api;

/**
 * Class to use instead of Facades\Api in tests, for IDE recognition of the $mock attribute.
 * @method static ApiResponseInterface  doRequest(RequestInterface $request, string $responseClass)
 * @method static string  getBaseUrl()
 * @method static MockHandler  getMock()
 *
 * @implements \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService
 */
class MockApi extends Api
{
}
