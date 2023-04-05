<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Plugin\Api\Contract\EndpointServiceInterface;
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
     * @return \MyParcelNL\Pdk\Api\Request\Request[]
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

    protected function getActionsByScope(string $scope): Collection
    {
        $actions = Config::get(sprintf('actions.%s', $scope));

        if (empty($actions)) {
            return new Collection();
        }

        return new Collection($actions);
    }
}
