<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Repository;

use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;

/**
 * @deprecated use Repository + PdkShippingMethodRepositoryInterface instead. Will be removed in v3.0.0
 * @todo       remove in v3.0.0
 */
abstract class AbstractPdkShippingMethodRepository extends Repository implements
    PdkShippingMethodRepositoryInterface
{
}
