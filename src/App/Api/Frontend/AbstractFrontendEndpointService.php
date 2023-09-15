<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api\Frontend;

use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Api\PdkEndpoints;
use MyParcelNL\Pdk\Base\Support\Collection;

abstract class AbstractFrontendEndpointService extends PdkEndpoints implements FrontendEndpointServiceInterface
{
    public function getEndpoints(): Collection
    {
        return $this->getActionsByScope(PdkEndpoint::CONTEXT_FRONTEND)
            ->merge($this->getActionsByScope(PdkEndpoint::CONTEXT_SHARED));
    }
}
