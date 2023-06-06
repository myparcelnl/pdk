<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Pdk;

final class MockPdk extends Pdk
{
    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->container->set($key, $value);
    }
}
