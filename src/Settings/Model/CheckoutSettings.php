<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $deliveryTitle
 * @property string $standardDeliveryTitle
 * @property string $morningDeliveryTitle
 * @property string $eveningDeliveryTitle
 * @property string $saturdayDeliveryTitle
 * @property string $signatureTitle
 * @property string $recipientTitle
 * @property string $onlyRecipientTitle
 * @property string $pickupTitle
 * @property string $houseNumberText
 * @property string $cityText
 * @property string $postalCodeText
 * @property string $countryText
 * @property string $openingHoursText
 * @property string $loadMoreTitle
 * @property string $pickupMapButtonTitle
 * @property string $pickupListButtonText
 * @property string $retryTitle
 * @property string $addressNotFoundTitle
 * @property string $wrongPostalCityCombinationTitle
 * @property string $wrongNumberPostalCodeTitle
 * @property string $fromTitle
 * @property string $discountTitle
 */
class CheckoutSettings extends Model
{
    protected $attributes = [
        'deliveryTitle'                   => null,
        'standardDeliveryTitle'           => null,
        'morningDeliveryTitle'            => null,
        'eveningDeliveryTitle'            => null,
        'saturdayDeliveryTitle'           => null,
        'signatureTitle'                  => null,
        'recipientTitle'                  => null,
        'onlyRecipientTitle'              => null,
        'pickupTitle'                     => null,
        'houseNumberText'                 => null,
        'cityText'                        => null,
        'postalCodeText'                  => null,
        'countryText'                     => null,
        'openingHoursText'                => null,
        'loadMoreTitle'                   => null,
        'pickupMapButtonTitle'            => null,
        'pickupListButtonText'            => null,
        'retryTitle'                      => null,
        'addressNotFoundTitle'            => null,
        'wrongPostalCityCombinationTitle' => null,
        'wrongNumberPostalCodeTitle'      => null,
        'fromTitle'                       => null,
        'discountTitle'                   => null,
    ];

    protected $casts      = [
        'deliveryTitle'                   => 'string',
        'standardDeliveryTitle'           => 'string',
        'morningDeliveryTitle'            => 'string',
        'eveningDeliveryTitle'            => 'string',
        'saturdayDeliveryTitle'           => 'string',
        'signatureTitle'                  => 'string',
        'recipientTitle'                  => 'string',
        'onlyRecipientTitle'              => 'string',
        'pickupTitle'                     => 'string',
        'houseNumberText'                 => 'string',
        'cityText'                        => 'string',
        'postalCodeText'                  => 'string',
        'countryText'                     => 'string',
        'openingHoursText'                => 'string',
        'loadMoreTitle'                   => 'string',
        'pickupMapButtonTitle'            => 'string',
        'pickupListButtonText'            => 'string',
        'retryTitle'                      => 'string',
        'addressNotFoundTitle'            => 'string',
        'wrongPostalCityCombinationTitle' => 'string',
        'wrongNumberPostalCodeTitle'      => 'string',
        'fromTitle'                       => 'string',
        'discountTitle'                   => 'string',
    ];
}
