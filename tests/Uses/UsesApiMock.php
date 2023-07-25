<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;

class UsesApiMock implements BaseMock
{
    public function afterEach(): void
    {
        MockApi::getMock()
            ->reset();
    }
}
