<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

class ClassWithGuardedAttributes extends Model
{
    protected $attributes = ['field' => 'test'];

    protected $guarded    = ['field' => 'test'];
}
