<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Backend;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Plugin\Api\PdkEndpoints;

abstract class AbstractPdkBackendEndpointService extends PdkEndpoints implements BackendEndpointServiceInterface
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection
    {
        return new Collection(Config::get('actions.backend'));
    }
}
