<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

class PlatformManager implements PlatformManagerInterface
{
    /**
     * @return array
     */
    public function all(): array
    {
        return Config::get(sprintf('platform/%s', $this->getPropositionName()));
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Config::get(sprintf('platform/%s.%s', $this->getPropositionName(), $key));
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
    public function getPropositionName(): string
    {
        $propositionName = Platform::MYPARCEL_NAME;

        try {
            $account = AccountSettings::getAccount();
            if ($account && Platform::SENDMYPARCEL_ID === $account->getAttribute('platformId')) {
                $propositionName = Platform::SENDMYPARCEL_NAME;
            }
        } catch (\Throwable $e) {
            // If the account settings are not available, we default to myparcel.
        }

        return $propositionName;
    }
}
