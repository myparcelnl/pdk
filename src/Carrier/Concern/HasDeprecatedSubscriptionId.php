<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Concern;

/**
 * @todo remove in v3.0.0
 */
trait HasDeprecatedSubscriptionId
{
    /**
     * @deprecated   use $contractId
     * @noinspection PhpUnused
     */
    public $subscriptionId;
}
