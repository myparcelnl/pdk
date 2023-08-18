<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Tests\Bootstrap\Facade\Mock;
use MyParcelNL\Pdk\Tests\Uses\Contract\BaseMock;

final class UsesMockPdkInstance implements BaseMock
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

    /**
     * @return void
     */
    public function beforeEach(): void
    {
        Mock::overrideMany($this->config);
    }
}
