<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

class UsesMockPdkInstance extends AbstractUsesMockPdkInstance
{
    public function afterAll(): void
    {
        $this->reset();
    }

    /**
     * @throws \Exception
     */
    public function beforeAll(): void
    {
        $this->setup();
    }
}
