<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Tests\Uses\Contract\BaseMock;

final class UsesMockPdkInstance implements BaseMock
{
    /**
     * @return void
     * @throws \Exception
     */
    public function beforeAll(): void
    {
        //        MockPdkFactory::create();
    }
}
