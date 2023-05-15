<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;

interface EndpointServiceInterface extends Arrayable
{
    /**
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection;
}
