<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * The proposition contracts model defines the available contracts for a proposition.
 * This determines the available carriers and carrier features for a proposition.
 *
 * @property array $availableForCustomCredentials which carrier contracts are available for custom credentials.
 */
class PropositionContracts extends Model
{
    protected $attributes = [
        'availableForCustomCredentials' => null,
    ];

    protected $casts = [
        'availableForCustomCredentials' => 'array',
    ];
}
