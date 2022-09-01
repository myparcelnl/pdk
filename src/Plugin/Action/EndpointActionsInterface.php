<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action;

use MyParcelNL\Pdk\Base\Support\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;

interface EndpointActionsInterface extends Arrayable
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function all(): Collection;

    /**
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection;
}
