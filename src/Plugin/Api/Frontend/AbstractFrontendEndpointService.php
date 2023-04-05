<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Frontend;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\PdkEndpoint;
use MyParcelNL\Pdk\Plugin\Api\PdkEndpoints;

abstract class AbstractFrontendEndpointService extends PdkEndpoints implements FrontendEndpointServiceInterface
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection
    {
        return $this->getActionsByScope(PdkEndpoint::CONTEXT_FRONTEND)
            ->merge($this->getActionsByScope(PdkEndpoint::CONTEXT_SHARED));
    }
}
