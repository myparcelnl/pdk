<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $addressNotFoundTitle
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $deliveryTitle
 * @property null|string $discount
 * @property null|string $eveningDeliveryTitle
 * @property null|string $from
 * @property null|string $houseNumber
 * @property null|string $loadMore
 * @property null|string $morningDeliveryTitle
 * @property null|string $onlyRecipientTitle
 * @property null|string $openingHours
 * @property null|string $pickupLocationsListButton
 * @property null|string $pickupLocationsMapButton
 * @property null|string $pickupTitle
 * @property null|string $postcode
 * @property null|string $recipientTitle
 * @property null|string $retry
 * @property null|string $saturdayDeliveryTitle
 * @property null|string $signatureTitle
 * @property null|string $standardDeliveryTitle
 * @property null|string $wrongNumberPostalCode
 * @property null|string $wrongPostalCodeCity
 */
class DeliveryOptionsStringsSettings extends Model
{
    /**
     * Settings in this category.
     */
    public const ADDRESS_NOT_FOUND            = 'addressNotFound';
    public const CC                           = 'cc';
    public const CITY                         = 'city';
    public const DELIVERY_TITLE               = 'deliveryTitle';
    public const DISCOUNT                     = 'discount';
    public const EVENING_DELIVERY_TITLE       = 'eveningDeliveryTitle';
    public const FROM                         = 'from';
    public const HOUSE_NUMBER                 = 'houseNumber';
    public const LOAD_MORE                    = 'loadMore';
    public const MORNING_DELIVERY_TITLE       = 'morningDeliveryTitle';
    public const ONLY_RECIPIENT_TITLE         = 'onlyRecipientTitle';
    public const OPENING_HOURS                = 'openingHours';
    public const PICKUP_LOCATIONS_LIST_BUTTON = 'pickupLocationsListButton';
    public const PICKUP_LOCATIONS_MAP_BUTTON  = 'pickupLocationsMapButton';
    public const PICKUP_TITLE                 = 'pickupTitle';
    public const POSTCODE                     = 'postcode';
    public const RECIPIENT_TITLE              = 'recipientTitle';
    public const RETRY                        = 'retry';
    public const SATURDAY_DELIVERY_TITLE      = 'saturdayDeliveryTitle';
    public const SIGNATURE_TITLE              = 'signatureTitle';
    public const STANDARD_DELIVERY_TITLE      = 'standardDeliveryTitle';
    public const WRONG_NUMBER_POSTAL_CODE     = 'wrongNumberPostalCode';
    public const WRONG_POSTAL_CODE_CITY       = 'wrongPostalCodeCity';

    protected $attributes = [
        self::ADDRESS_NOT_FOUND            => null,
        self::CC                           => null,
        self::CITY                         => null,
        self::DELIVERY_TITLE               => null,
        self::DISCOUNT                     => null,
        self::EVENING_DELIVERY_TITLE       => null,
        self::FROM                         => null,
        self::HOUSE_NUMBER                 => null,
        self::LOAD_MORE                    => null,
        self::MORNING_DELIVERY_TITLE       => null,
        self::ONLY_RECIPIENT_TITLE         => null,
        self::OPENING_HOURS                => null,
        self::PICKUP_LOCATIONS_LIST_BUTTON => null,
        self::PICKUP_LOCATIONS_MAP_BUTTON  => null,
        self::PICKUP_TITLE                 => null,
        self::POSTCODE                     => null,
        self::RECIPIENT_TITLE              => null,
        self::RETRY                        => null,
        self::SATURDAY_DELIVERY_TITLE      => null,
        self::SIGNATURE_TITLE              => null,
        self::STANDARD_DELIVERY_TITLE      => null,
        self::WRONG_NUMBER_POSTAL_CODE     => null,
        self::WRONG_POSTAL_CODE_CITY       => null,
    ];

    protected $casts      = [
        self::ADDRESS_NOT_FOUND            => 'string',
        self::CC                           => 'string',
        self::CITY                         => 'string',
        self::DELIVERY_TITLE               => 'string',
        self::DISCOUNT                     => 'string',
        self::EVENING_DELIVERY_TITLE       => 'string',
        self::FROM                         => 'string',
        self::HOUSE_NUMBER                 => 'string',
        self::LOAD_MORE                    => 'string',
        self::MORNING_DELIVERY_TITLE       => 'string',
        self::ONLY_RECIPIENT_TITLE         => 'string',
        self::OPENING_HOURS                => 'string',
        self::PICKUP_LOCATIONS_LIST_BUTTON => 'string',
        self::PICKUP_LOCATIONS_MAP_BUTTON  => 'string',
        self::PICKUP_TITLE                 => 'string',
        self::POSTCODE                     => 'string',
        self::RECIPIENT_TITLE              => 'string',
        self::RETRY                        => 'string',
        self::SATURDAY_DELIVERY_TITLE      => 'string',
        self::SIGNATURE_TITLE              => 'string',
        self::STANDARD_DELIVERY_TITLE      => 'string',
        self::WRONG_NUMBER_POSTAL_CODE     => 'string',
        self::WRONG_POSTAL_CODE_CITY       => 'string',
    ];
}
