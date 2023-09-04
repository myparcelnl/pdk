<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Facade;
use Psr\Http\Message\RequestInterface;

/**
 * @method static RequestInterface ensureLastRequest()
 * @method static array|null getLastRequestBody()
 * @method static string getBaseUrl()
 * @method static null|RequestInterface getLastRequest()
 * @method static MockHandler getMock()
 * @method static void enqueue(Response ...$responses)
 * @implements \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService
 */
final class MockApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiServiceInterface::class;
    }
}
