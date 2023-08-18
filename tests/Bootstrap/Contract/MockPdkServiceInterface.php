<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Contract;

use MyParcelNL\Pdk\Contract\MockServiceInterface;

interface MockPdkServiceInterface extends MockServiceInterface
{
    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function override(string $key, $value): void;

    /**
     * @param  array $config
     *
     * @return void
     */
    public function overrideMany(array $config): void;
}
