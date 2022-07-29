<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Support\Collection;

class MockCastingCollection extends Collection
{
    protected $cast = MockCastModel::class;
}
