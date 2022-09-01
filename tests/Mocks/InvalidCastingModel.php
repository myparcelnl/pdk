<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

class InvalidCastingModel extends Model
{
    protected $attributes = ['value' => null];

    protected $casts      = ['value' => MockCastModel::class];
}
