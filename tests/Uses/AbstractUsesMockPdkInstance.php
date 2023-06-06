<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

abstract class AbstractUsesMockPdkInstance implements BaseMock
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    protected function reset(): void
    {
        Pdk::setPdkInstance(null);
    }

    /**
     * @throws \Exception
     */
    protected function setup(): void
    {
        PdkFactory::create(MockPdkConfig::create($this->config));
    }
}
