<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

class MockTraitModel extends Model
{
    use MockHasCatsTrait;

    protected $attributes = [];
}
