<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;

class PropositionAvailableContract extends Model
{
    protected $attributes = [
        'id' => null,
        'carrier' => PropositionCarrierMetadata::class,
        'hasPostponedDelivery' => null,
        'inboundFeatures' => PropositionCarrierFeatures::class,
        'outboundFeatures' => PropositionCarrierFeatures::class,
    ];

    protected $casts = [
        'id' => 'int',
        'carrier' => PropositionCarrierMetadata::class,
        'hasPostponedDelivery' => 'bool',
        'inboundFeatures' => PropositionCarrierFeatures::class,
        'outboundFeatures' => PropositionCarrierFeatures::class,
    ];
}
