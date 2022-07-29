<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $ageCheck
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $dropOffAtPostalPoint
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $insurance
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $labelDescription
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $largeFormat
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $onlyRecipient
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $return
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $sameDayDelivery
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $saturdayDelivery
 * @property \MyParcelNL\Pdk\Carrier\Model\Capability $signature
 */
class ShipmentOptionsCapabilities extends Model
{
    protected $attributes = [
        'ageCheck'             => Capability::class,
        'dropOffAtPostalPoint' => Capability::class,
        'insurance'            => Capability::class,
        'labelDescription'     => Capability::class,
        'largeFormat'          => Capability::class,
        'onlyRecipient'        => Capability::class,
        'return'               => Capability::class,
        'sameDayDelivery'      => Capability::class,
        'saturdayDelivery'     => Capability::class,
        'signature'            => Capability::class,
    ];

    public function __construct(?array $data = null)
    {
        $this->casts = $this->attributes;
        parent::__construct($data);
    }
}
