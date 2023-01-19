<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use DI\Container;
use MyParcelNL\Pdk\Base\Exception\PdkConfigException;
use Throwable;

final class Pdk
{
    public const  PACKAGE_NAME           = 'myparcelnl/pdk';
    public const  MODE_DEVELOPMENT       = 'development';
    public const  MODE_PRODUCTION        = 'production';
    private const REQUIRED_APP_INFO_KEYS = [
        'name',
        'title',
        'path',
        'version',
    ];

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
     * @return array{name: string, path: string, title: string, version: string}
     * @throws \MyParcelNL\Pdk\Base\Exception\PdkConfigException
     * @noinspection PhpUnused
     */
    public function getAppInfo(): array
    {
        try {
            $appInfo = $this->get('appInfo');
        } catch (Throwable $e) {
            throw new PdkConfigException('The appInfo property is missing.');
        }

        $keys = array_diff(self::REQUIRED_APP_INFO_KEYS, array_keys($appInfo));

        // if any key is missing or not a string
        if (count($keys) > 0 || count(array_filter($appInfo, 'is_string')) !== count($appInfo)) {
            throw new PdkConfigException('The appInfo property is incomplete.', [
                'missing' => array_diff(self::REQUIRED_APP_INFO_KEYS, array_keys($appInfo)),
            ]);
        }

        return $appInfo;
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
