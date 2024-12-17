<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property bool   $ageCheck
 * @property bool   $collect
 * @property bool   $cooledDelivery
 * @property string $deliveryDate
 * @property int    $deliveryType
 * @property bool   $dropOffAtPostalPoint
 * @property bool   $hideSender
 * @property int    $insurance
 * @property string $labelDescription
 * @property bool   $largeFormat
 * @property bool   $onlyRecipient
 * @property int    $packageType
 * @property bool   $return
 * @property bool   $sameDayDelivery
 * @property bool   $saturdayDelivery
 * @property bool   $signature
 */
class ShipmentOptions extends Model
{
    public $attributes = [
        'ageCheck'         => null,
        'collect'          => null,
        'cooledDelivery'   => null,
        'deliveryDate'     => null,
        'deliveryType'     => null,
        'hideSender'       => null,
        'insurance'        => null,
        'labelDescription' => '',
        'largeFormat'      => null,
        'onlyRecipient'    => null,
        'packageType'      => null,
        'return'           => null,
        'sameDayDelivery'  => null,
        'saturdayDelivery' => null,
        'signature'        => null,
    ];

    public $casts      = [
        'ageCheck'         => 'bool',
        'collect'          => 'bool',
        'cooledDelivery'   => 'bool',
        'deliveryDate'     => 'string',
        'deliveryType'     => 'int',
        'hideSender'       => 'bool',
        'insurance'        => 'int',
        'labelDescription' => 'string',
        'largeFormat'      => 'bool',
        'onlyRecipient'    => 'bool',
        'packageType'      => 'int',
        'return'           => 'bool',
        'sameDayDelivery'  => 'bool',
        'saturdayDelivery' => 'bool',
        'signature'        => 'bool',
    ];

    /**
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $pdkDeliveryOptions
     *
     * @return static
     */
    public static function fromPdkDeliveryOptions(?DeliveryOptions $pdkDeliveryOptions): self
    {
        if (! $pdkDeliveryOptions) {
            return new static();
        }

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $orderOptionsService */
        $orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);

        $calculated = $orderOptionsService->calculateShipmentOptions(
            new PdkOrder(['deliveryOptions' => $pdkDeliveryOptions]),
            PdkOrderOptionsServiceInterface::EXCLUDE_PRODUCT_SETTINGS | PdkOrderOptionsServiceInterface::EXCLUDE_CARRIER_SETTINGS
        );

        return new static(
            array_replace($calculated->deliveryOptions->shipmentOptions->getAttributes(), [
                'packageType'  => $pdkDeliveryOptions->getPackageTypeId(),
                'deliveryType' => $pdkDeliveryOptions->getDeliveryTypeId(),
                'deliveryDate' => $pdkDeliveryOptions->date
                    ? $pdkDeliveryOptions->date->format(Pdk::get('defaultDateFormat'))
                    : null,
            ])
        );
    }
}
