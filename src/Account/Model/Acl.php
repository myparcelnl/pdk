<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|array $permissions
 * @property null|array $roles
 * @property null|array $shopIds
 * @property null|array $subscriptionFeatures
 */
class Acl extends Model implements StorableArrayable
{
    public $attributes = [
        'permissions'          => null,
        'roles'                => null,
        'shopIds'              => null,
        'subscriptionFeatures' => null,
    ];

    public $casts      = [
        'permissions'          => 'array',
        'roles'                => 'array',
        'shopIds'              => 'array',
        'subscriptionFeatures' => 'array',
    ];

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return $this->toArrayWithoutNull();
    }
}
