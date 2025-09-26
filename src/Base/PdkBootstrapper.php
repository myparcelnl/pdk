<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Base\Contract\PdkBootstrapperInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use function DI\value;

class PdkBootstrapper implements PdkBootstrapperInterface
{
    public const PLUGIN_NAMESPACE = 'myparcelcom';

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
     * @param  string $version
     * @param  string $path
     * @param  string $url
     * @param  string $mode
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    final public static function boot(
        string $version,
        string $path,
        string $url,
        string $mode = Pdk::MODE_PRODUCTION
    ): Pdk {
        if (! self::$initialized) {
            PdkFactory::setMode($mode);

            self::$initialized = true;
            self::$pdk = PdkFactory::create(
                "$path/config/pdk.php",
                [
                    'appInfo'  => value(
                        new AppInfo([
                            'name'    => self::PLUGIN_NAMESPACE,
                            'title'   => 'MyParcel',
                            'version' => $version,
                            'path'    => $path,
                            'url'     => $url,
                        ])
                    ),
                ],
                (new static())->getAdditionalConfig(
                    self::PLUGIN_NAMESPACE,
                    'MyParcel',
                    $version,
                    $path,
                    $url
                ),
            );
        }

        return self::$pdk;
    }

    /**
     * Not static because that cannot be overridden in child classes.
     *
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
}
