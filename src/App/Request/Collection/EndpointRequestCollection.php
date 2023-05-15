<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Collection;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property array{string, array{label: string, request: AbstractEndpointRequest}}[] $items
 */
class EndpointRequestCollection extends Collection
{
}
