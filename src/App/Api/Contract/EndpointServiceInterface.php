<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;

interface EndpointServiceInterface extends Arrayable
{
    /**
     * The base url of the API.
     */
    public function getBaseUrl(): string;

    /**
     * All available endpoints.
     */
    public function getEndpoints(): Collection;
}
