<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\PdkBootstrapperInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use function DI\value;

class PdkBootstrapper implements PdkBootstrapperInterface
{
    /**
     * @var bool
     */
    protected static $initialized = false;

    /**
     * @var \MyParcelNL\Pdk\Base\Pdk
     */
    protected static $pdk;

    public function __construct() { }

    /**
     * @throws \Exception
     */
    final public static function boot(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url,
        string $mode = Pdk::MODE_PRODUCTION
    ): Pdk {
        if (! self::$initialized) {
            PdkFactory::setMode($mode);

            self::$initialized = true;
            self::$pdk         = (new static())->createPdkInstance($name, $title, $version, $path, $url);
        }

        return self::$pdk;
    }

    /**
     * @throws \Exception
     */
    protected function createPdkInstance(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): PdkInterface {
        $appInfo = new AppInfo([
            'name'    => $name,
            'title'   => $title,
            'version' => $version,
            'path'    => $path,
            'url'     => $url,
        ]);

        return PdkFactory::create(
            implode('/', [$path, $this->getConfigPath()]),
            [
                'appInfo'  => value($appInfo),
                'platform' => value($this->determinePlatform($name)),
            ],
            $this->getAdditionalConfig($name, $title, $version, $path, $url)
        );
    }

    protected function determinePlatform(string $name): string
    {
        return match ($name) {
            'myparcelbe', Platform::SENDMYPARCEL_NAME => Platform::SENDMYPARCEL_NAME,
            Platform::FLESPAKKET_NAME => Platform::FLESPAKKET_NAME,
            default => Platform::MYPARCEL_NAME,
        };
    }

    /**
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

    protected function getConfigPath(): string
    {
        return 'config/pdk.php';
    }
}
