<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

final class MockConfig extends Config
{
    public const SUBSCRIPTION_ID_DHL_FOR_YOU = 23182;

    /**
     * @var array
     */
    private $config;

    /**
     * @param  array $data
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $data = [], FileSystemInterface $fileSystem)
    {
        parent::__construct($fileSystem);

        $carriers = $this->getFromRealConfig('carriers');

        $defaultDhl = (new Collection($carriers))->firstWhere('id', Carrier::CARRIER_DHL_FOR_YOU_ID);

        // Add a custom carrier to the config
        $carrierConfig = array_merge($carriers, [
            array_merge(
                $defaultDhl,
                [
                    'subscriptionId' => self::SUBSCRIPTION_ID_DHL_FOR_YOU,
                    'type'           => Carrier::TYPE_CUSTOM,
                ]
            ),
        ]);

        $this->config = array_replace_recursive(['carriers' => $carrierConfig], $data);
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        if (! Arr::has($this->config, $key)) {
            return $this->getFromRealConfig($key);
        }

        return Arr::get($this->config, $key);
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    private function getFromRealConfig(string $key)
    {
        /** @var \MyParcelNL\Pdk\Base\Config $config */
        $config = Pdk::get(Config::class);

        return $config->get($key);
    }
}
