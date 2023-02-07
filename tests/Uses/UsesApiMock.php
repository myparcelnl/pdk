<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;

class UsesApiMock implements BaseMock
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    public $mock;

    public function afterEach(): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
        $api = Pdk::get(ApiServiceInterface::class);

        $api
            ->getMock()
            ->reset();
    }
}
