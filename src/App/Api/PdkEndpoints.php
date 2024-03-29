<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Api;

use MyParcelNL\Pdk\App\Api\Contract\EndpointServiceInterface;
use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;

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
     * @param  null|int $flags
     *
     * @return \MyParcelNL\Pdk\Api\Request\Request[]
     */
    public function toArray(?int $flags = null): array
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
            ->toArrayWithoutNull();
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
