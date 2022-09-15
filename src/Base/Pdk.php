<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use DI\Container;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Plugin\Action\PdkActionManager;
use Symfony\Component\HttpFoundation\Response;

class Pdk
{
    public const PACKAGE_NAME     = 'myparcelnl/pdk';
    public const MODE_DEVELOPMENT = 'development';
    public const MODE_PRODUCTION  = 'production';
    public const DEFAULT_API_URL  = 'api.myparcel.nl';

    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param  string $action
     * @param  array  $params
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Exception
     */
    public function execute(string $action, array $params = []): ?Response
    {
        /** @var \MyParcelNL\Pdk\Plugin\Action\PdkActionManager $manager */
        $manager = $this->get(PdkActionManager::class);

        $params['action'] = $action;

        return $manager->execute($params);
    }

    /**
     * @param  string $key
     *
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $key)
    {
        return $this->container->get($key);
    }

    /**
     * @return string
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getMode(): string
    {
        return $this->get('mode');
    }

    /**
     * @throws \DI\NotFoundException
     * @throws \DI\DependencyException
     */
    public function getApiBaseUrl(): string
    {
        return $this->get(ApiServiceInterface::class)->getBaseUrl();
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->container->has($key);
    }

    /**
     * @return bool
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @noinspection PhpUnused
     */
    public function isDevelopment(): bool
    {
        return self::MODE_DEVELOPMENT === $this->getMode();
    }

    /**
     * @return bool
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @noinspection PhpUnused
     */
    public function isProduction(): bool
    {
        return self::MODE_PRODUCTION === $this->getMode();
    }
}
