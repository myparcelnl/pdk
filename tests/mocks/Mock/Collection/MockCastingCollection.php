<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Mock\Model\MockCastModel;

final class MockCastingCollection extends Collection
{
    protected $cast = MockCastModel::class;
}
