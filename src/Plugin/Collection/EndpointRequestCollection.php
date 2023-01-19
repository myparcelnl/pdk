<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

/**
 * @property array{string, array{label: string, request: AbstractEndpointRequest}}[] $items
 */
class EndpointRequestCollection extends Collection
{
}
