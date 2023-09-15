<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Pdk;

final class MockPdk extends Pdk
{
    public function set(string $key, mixed $value): void
    {
        $this->container->set($key, $value);
    }
}
