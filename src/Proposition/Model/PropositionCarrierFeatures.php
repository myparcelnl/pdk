<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * The proposition carrier features model defines the features for a carrier.
 * @package MyParcelNL\Pdk\Proposition
 */
class PropositionCarrierFeatures
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
