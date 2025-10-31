<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

class PlatformManager implements PlatformManagerInterface
{
    /**
     * @return array
     */
    public function all(): array
    {
        return Config::get(sprintf('platform/%s', $this->getPlatform()));
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Config::get(sprintf('platform/%s.%s', $this->getPlatform(), $key));
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getCarriers(): CarrierCollection
    {
        return Pdk::get('carriers');
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return (string) Pdk::get('platform');
    }

    /**
     * @return string
     * @deprecated Use getPropositionName() from PropositionManager via Proposition facade instead
     */
    public function getPropositionName(): string
    {
        return $this->getPlatform();
    }
}
