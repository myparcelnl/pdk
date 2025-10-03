<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * The proposition carrier metadata model defines the metadata for a carrier.
 * @package MyParcelNL\Pdk\Proposition
 *
 * @property int $id The unique identifier for the carrier.
 * @property string $name The name of the carrier.
 */
class PropositionCarrierMetadata extends Model
{
    /**
     * Feature value used to indicate that the feature is only available for custom contracts.
     */
    public const FEATURE_CUSTOM_CONTRACT_ONLY = 'FEATURE_CUSTOM_CONTRACT_ONLY';

    /**
     * The name of the option that defines the insurable amounts.
     */
    public const FEATURE_NAME_INSURANCE_OPTIONS = 'insuranceOptions';

    /**
     * Whether the carrier requires personal information from the customer.
     */
    public const FEATURE_NAME_NEEDS_CUSTOMER_INFO = 'needsCustomerInfo';

    protected $attributes = [
        'id' => null,
        'name' => null,
    ];

    protected $casts = [
        'id' => 'int',
        'name' => 'string'
    ];
}
