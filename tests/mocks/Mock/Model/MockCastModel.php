<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class MockCastModel extends Model
{
    protected $attributes = ['property' => null];

    protected $deprecated = [
        'broccoli' => 'property',
    ];
}
