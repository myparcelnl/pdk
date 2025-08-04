<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Proposition\Collection\PropositionAvailableContractsCollection;

/**
 * The proposition contracts model defines the available contracts for a proposition.
 * This determines the available carriers and carrier features for a proposition.
 *
 * @property PropositionAvailableContractsCollection $available which carrier contracts are available for this proposition.
 * @property array $availableForCustomCredentials which carrier contracts are available for custom credentials.
 * @property array $inbound defines the default inbound contracts for this proposition. (Inbound contracts are used for receiving shipments eg. returns)
 * @property array $outbound defines the default outbound contracts for this proposition.
 */
class PropositionContracts extends Model
{
    protected $attributes = [
        'available' => PropositionAvailableContractsCollection::class,
        'availableForCustomCredentials' => null,
        'inbound' => ['default' => []], // @TODO type
        'outbound' => ['default' => []], // @TODO type
    ];

    protected $casts = [
        'available' => PropositionAvailableContractsCollection::class,
        'availableForCustomCredentials' => 'array',
        'inbound' => 'array',
// @TODO type

        'outbound' => 'array',// @TODO type
    ];
}
