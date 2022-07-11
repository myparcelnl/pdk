<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool|null $ageCheck
 * @property int|null  $insurance
 * @property bool|null $labelDescription
 * @property bool|null $largeFormat
 * @property bool|null $onlyRecipient
 * @property bool|null $return
 * @property bool|null $sameDayDelivery
 * @property bool|null $signature
 */
class ShipmentOptions extends Model
{
    protected $attributes = [
        'ageCheck'         => null,
        'insurance'        => null,
        'labelDescription' => null,
        'largeFormat'      => null,
        'onlyRecipient'    => null,
        'return'           => null,
        'sameDayDelivery'  => null,
        'signature'        => null,
    ];
}
