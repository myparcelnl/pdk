<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\DropOffDayPossibilities;

/**
 * @property bool                                                  $allowDeliveryOptions
 * @property bool                                                  $allowEveningDelivery
 * @property bool                                                  $allowMondayDelivery
 * @property bool                                                  $allowMorningDelivery
 * @property bool                                                  $allowOnlyRecipient
 * @property bool                                                  $allowPickupLocations
 * @property bool                                                  $allowSameDayDelivery
 * @property bool                                                  $allowSaturdayDelivery
 * @property bool                                                  $allowSignature
 * @property string                                                $cutoffTime
 * @property string                                                $cutoffTimeSameDay
 * @property \MyParcelNL\Pdk\Carrier\Model\DropOffDayPossibilities $dropOffDays
 * @property bool                                                  $featureShowDeliveryDate
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceEveningDelivery
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceMorningDelivery
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceOnlyRecipient
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $pricePackageTypeDigitalStamp
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $pricePackageTypeMailbox
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $pricePickup
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceSameDayDelivery
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceSignature
 * @property \MyParcelNL\Pdk\Base\Model\Currency                   $priceStandardDelivery
 * @property \MyParcelNL\Pdk\Settings\Model\LabelSettings          $labelSettings
 * @property \MyParcelNL\Pdk\Settings\Model\CheckoutSettings       $checkoutSettings
 * @property \MyParcelNL\Pdk\Settings\Model\CustomsSettings        $customsSettings
 * @property \MyParcelNL\Pdk\Settings\Model\OrderSettings          $orderSettings
 */
class CarrierSettings extends Model
{
    protected $attributes = [
        'allowDeliveryOptions'         => false,
        'allowEveningDelivery'         => false,
        'allowMondayDelivery'          => false,
        'allowMorningDelivery'         => false,
        'allowOnlyRecipient'           => false,
        'allowPickupLocations'         => false,
        'allowSameDayDelivery'         => false,
        'allowSaturdayDelivery'        => false,
        'allowSignature'               => false,
        'cutoffTime'                   => null,
        'cutoffTimeSameDay'            => null,
        'dropOffDays'                  => DropOffDayPossibilities::class,
        'featureShowDeliveryDate'      => null,
        'priceEveningDelivery'         => null,
        'priceMorningDelivery'         => null,
        'priceOnlyRecipient'           => null,
        'pricePackageTypeDigitalStamp' => null,
        'pricePackageTypeMailbox'      => null,
        'pricePickup'                  => null,
        'priceSameDayDelivery'         => null,
        'priceSignature'               => null,
        'priceStandardDelivery'        => null,
        'labelSettings'                => LabelSettings::class,
        'checkoutSettings'             => CheckoutSettings::class,
        'customsSettings'              => CustomsSettings::class,
        'orderSettings'                => OrderSettings::class,
    ];

    protected $casts      = [
        'allowedDeliveryOptions'       => 'bool',
        'allowEveningDelivery'         => 'bool',
        'allowMondayDelivery'          => 'bool',
        'allowMorningDelivery'         => 'bool',
        'allowOnlyRecipient'           => 'bool',
        'allowPickupLocations'         => 'bool',
        'allowSameDayDelivery'         => 'bool',
        'allowSaturdayDelivery'        => 'bool',
        'allowSignature'               => 'bool',
        'cutoffTime'                   => 'string',
        'cutoffTimeSameDay'            => 'string',
        'deliveryDaysWindow'           => null,
        'dropOffDays'                  => null,
        'dropOffDelay'                 => null,
        'featureShowDeliveryDate'      => null,
        'priceEveningDelivery'         => Currency::class,
        'priceMorningDelivery'         => Currency::class,
        'priceOnlyRecipient'           => Currency::class,
        'pricePackageTypeDigitalStamp' => Currency::class,
        'pricePackageTypeMailbox'      => Currency::class,
        'pricePickup'                  => Currency::class,
        'priceSameDayDelivery'         => Currency::class,
        'priceSignature'               => Currency::class,
        'priceStandardDelivery'        => Currency::class,
        'labelSettings'                => LabelSettings::class,
        'checkoutSettings'             => CheckoutSettings::class,
        'customsSettings'              => CustomsSettings::class,
        'orderSettings'                => OrderSettings::class,
    ];
}
