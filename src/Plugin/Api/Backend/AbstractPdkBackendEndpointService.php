<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Backend;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\PdkEndpoint;
use MyParcelNL\Pdk\Plugin\Api\PdkEndpoints;

abstract class AbstractPdkBackendEndpointService extends PdkEndpoints implements BackendEndpointServiceInterface
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection
    {
        return $this->getActionsByScope(PdkEndpoint::CONTEXT_BACKEND)
            ->merge($this->getActionsByScope(PdkEndpoint::CONTEXT_SHARED));
    }
}
