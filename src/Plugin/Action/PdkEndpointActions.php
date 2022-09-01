<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;
use MyParcelNL\Sdk\src\Support\Arr;

abstract class PdkEndpointActions implements EndpointActionsInterface
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
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function all(): Collection
    {
        return $this
            ->getEndpoints()
            ->map(function (array $config, string $action): AbstractEndpointRequest {
                $requestClass = Arr::get($config, 'request');

                return new $requestClass([
                    'headers'    => $this->headers,
                    'parameters' => ['action' => $action] + $this->parameters,
                ]);
            });
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getEndpoints(): Collection
    {
        return new Collection(Config::get('actions.endpoints'));
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Request\Request[]
     */
    public function toArray(): array
    {
        return $this
            ->all()
            ->toArray();
    }
}
