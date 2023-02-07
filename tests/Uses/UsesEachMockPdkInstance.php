<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

class UsesEachMockPdkInstance extends AbstractUsesMockPdkInstance
{
    public function afterEach(): void
    {
        $this->reset();
    }

    /**
     * @throws \Exception
     */
    public function beforeEach(): void
    {
        $this->setup();
    }
}
