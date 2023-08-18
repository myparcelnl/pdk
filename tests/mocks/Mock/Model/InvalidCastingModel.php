<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class InvalidCastingModel extends Model
{
    protected $attributes = ['value' => null];

    protected $casts      = ['value' => MockCastModel::class];
}
