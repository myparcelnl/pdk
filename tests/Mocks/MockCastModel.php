<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string|null $property
 * @property string|null $broccoli
 */
class MockCastModel extends Model
{
    protected $attributes = ['property' => null];

    protected $deprecated = [
        'broccoli' => 'property',
    ];
}
