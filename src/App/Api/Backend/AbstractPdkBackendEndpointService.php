<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Backend;

use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Api\PdkEndpoints;
use MyParcelNL\Pdk\Base\Support\Collection;

abstract class AbstractPdkBackendEndpointService extends PdkEndpoints implements BackendEndpointServiceInterface
{
    public function getEndpoints(): Collection
    {
        return $this->getActionsByScope(PdkEndpoint::CONTEXT_BACKEND)
            ->merge($this->getActionsByScope(PdkEndpoint::CONTEXT_SHARED));
    }
}
