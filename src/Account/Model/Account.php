<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\StorableArrayable;

/**
 * @property int                    $id
 * @property int                    $platformId
 * @property int                    $status
 * @property ContactDetails         $contactInfo
 * @property AccountGeneralSettings $generalSettings
 * @property ShopCollection         $shops
 */
class Account extends Model implements StorableArrayable
{
    public $attributes = [
        'id'              => null,
        'platformId'      => null,
        'status'          => null,
        'contactInfo'     => ContactDetails::class,
        'generalSettings' => AccountGeneralSettings::class,
        'shops'           => ShopCollection::class,
    ];

    public $casts      = [
        'id'              => 'int',
        'platformId'      => 'int',
        'status'          => 'int',
        'contactInfo'     => ContactDetails::class,
        'generalSettings' => AccountGeneralSettings::class,
        'shops'           => ShopCollection::class,
    ];

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return $this->toArrayWithoutNull();
    }
}
