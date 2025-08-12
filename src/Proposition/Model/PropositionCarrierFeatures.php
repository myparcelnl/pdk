<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * The proposition carrier features model defines the features for a carrier.
 *
 * @property array $packageTypes The package types supported by the carrier.
 * @property array $deliveryTypes The delivery types supported by the carrier.
 * @property array $shipmentOptions The shipment options supported by the carrier.
 * @property array $metadata The metadata for the carrier, including features.
 *
 * @package MyParcelNL\Pdk\Proposition
 */
class PropositionCarrierFeatures extends Model
{
    protected $attributes = [
        'packageTypes' => null,
        'deliveryTypes' => null,
        'shipmentOptions' => null,
        'metadata' => null
    ];

    /**
     * @todo convert to enums in the future (PHP 8.1+)
     */
    protected $casts = [
        'packageTypes' => 'array',
        'deliveryTypes' => 'array',
        'shipmentOptions' => 'array',
        'metadata' => 'array',
    ];
}
