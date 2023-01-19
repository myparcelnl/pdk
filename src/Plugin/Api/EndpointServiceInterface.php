<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api;

use MyParcelNL\Pdk\Base\Support\Arrayable;
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
