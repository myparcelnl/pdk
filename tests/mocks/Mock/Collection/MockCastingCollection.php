<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;

final class MockCastingCollection extends Collection
{
    protected $cast = MockCastModel::class;
}
