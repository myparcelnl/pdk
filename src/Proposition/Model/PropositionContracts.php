<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * The proposition contracts model defines the available contracts for a proposition.
 * This determines the available carriers and carrier features for a proposition.
 *
 * @property array $availableForCustomCredentials which carrier contracts are available for custom credentials.
 * @property array $inbound defines the default inbound contracts for this proposition. (Inbound contracts are used for receiving shipments eg. returns)
 * @property array $outbound defines the default outbound contracts for this proposition.
 */
class PropositionContracts extends Model
{
    protected $attributes = [
        'availableForCustomCredentials' => null,
        'inbound' => ['default' => []],
        'outbound' => ['default' => []],
    ];

    protected $casts = [
        'availableForCustomCredentials' => 'array',
        'inbound' => 'array',
        'outbound' => 'array',
    ];
}
