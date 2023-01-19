<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

abstract class PdkEndpoints implements EndpointServiceInterface
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @return \MyParcelNL\Pdk\Base\Request\Request[]
     */
    public function toArray(): array
    {
        return $this
            ->getEndpoints()
            ->map(function (array $config, string $action): AbstractEndpointRequest {
                $requestClass = Arr::get($config, 'request');

                return new $requestClass([
                    'headers'    => $this->headers,
                    'parameters' => ['action' => $action] + $this->parameters,
                ]);
            })
            ->toArray();
    }
}
