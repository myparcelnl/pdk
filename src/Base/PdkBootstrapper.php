<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use function DI\factory;
use function DI\value;

class PdkBootstrapper
{
    /**
     * @var bool
     */
    protected static $initialized = false;

    /**
     * @var \MyParcelNL\Pdk\Base\Pdk
     */
    protected static $pdk;

    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    final public static function boot(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): Pdk {
        if (! self::$initialized) {
            self::$initialized = true;
            self::$pdk         = (new static())->createPdkInstance($name, $title, $version, $path, $url);
        }

        return self::$pdk;
    }

    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    protected function createPdkInstance(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): Pdk {
        return PdkFactory::create(
            implode('/', [$path, $this->getConfigPath()]),
            [
                'appInfo'  => factory(function () use ($name, $title, $version, $path, $url) {
                    return new AppInfo([
                        'name'    => $name,
                        'title'   => $title,
                        'version' => $version,
                        'path'    => $path,
                        'url'     => $url,
                    ]);
                }),
                'platform' => value($this->determinePlatform($name)),
            ],
            $this->getAdditionalConfig($name, $title, $version, $path, $url)
        );
    }

    /**
     * @param  string $name
     *
     * @return string
     */
    protected function determinePlatform(string $name): string
    {
        switch ($name) {
            case 'myparcelbe':
            case Platform::SENDMYPARCEL_NAME:
                return Platform::SENDMYPARCEL_NAME;

            case Platform::FLESPAKKET_NAME:
                return Platform::FLESPAKKET_NAME;

            default:
                return Platform::MYPARCEL_NAME;
        }
    }

    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function getAdditionalConfig(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): array {
        return [];
    }

    /**
     * @return string
     */
    protected function getConfigPath(): string
    {
        return 'config/pdk.php';
    }
}
