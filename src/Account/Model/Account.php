<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCollection;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property int                    $id
 * @property int                    $platformId
 * @property ContactDetails         $contactInfo
 * @property AccountGeneralSettings $generalSettings
 * @property ShopCollection         $shops
 * @property int                    $status
 * @property array                  $subscriptionFeatures
 */
class Account extends Model implements StorableArrayable
{
    public const FEATURE_ORDER_NOTES = 'allow_order_notes';

    public $attributes = [
        'id'                   => null,
        'platformId'           => null,
        'contactInfo'          => ContactDetails::class,
        'generalSettings'      => AccountGeneralSettings::class,
        'shops'                => ShopCollection::class,
        'status'               => null,
        'subscriptionFeatures' => Collection::class,
    ];

    public $casts      = [
        'id'                   => 'int',
        'platformId'           => 'int',
        'contactInfo'          => ContactDetails::class,
        'generalSettings'      => AccountGeneralSettings::class,
        'shops'                => ShopCollection::class,
        'status'               => 'int',
        'subscriptionFeatures' => Collection::class,
    ];

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return $this->toArrayWithoutNull();
    }
}
