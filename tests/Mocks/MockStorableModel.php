<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property array|null $property
 */
class MockStorableModel extends Model implements StorableArrayable
{
    protected $attributes = [
        'property' => null,
    ];

    protected $casts      = [
        'property' => 'array',
    ];

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return [
            'property' => json_encode($this->property),
        ];
    }
}
