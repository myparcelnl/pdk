<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

trait MockHasCatsTrait
{
    public function initializeMockHasCatsTrait(): void
    {
        $this->attributes['cat1'] = 'Gouda';
        $this->attributes['cat2'] = 'Mocha';
    }
}
