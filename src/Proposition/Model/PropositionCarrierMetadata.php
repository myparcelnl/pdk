<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

/**
 * The proposition carrier metadata model defines the metadata for a carrier.
 * @package MyParcelNL\Pdk\Proposition
 *
 * @property int $id The unique identifier for the carrier.
 * @property string $name The name of the carrier.
 */
class PropositionCarrierMetadata
{
    protected $attributes = [
        'id' => null,
        'name' => null,
    ];

    protected $casts = [
        'id' => 'int',
        'name' => 'string'
    ];
}
