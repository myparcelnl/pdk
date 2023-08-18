<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Model;

use MyParcelNL\Pdk\Base\Model\Model;

final class MockMutateModel extends Model
{
    protected $attributes = [
        'myProperty' => 1,
        'perenboom'  => null,
        'bloemkool'  => null,
    ];

    public function getBloemkoolAttribute(): string
    {
        return 'bloemkool';
    }

    public function setPerenboomAttribute($value): self
    {
        $this->attributes['perenboom'] = "mutated_$value";
        return $this;
    }
}
