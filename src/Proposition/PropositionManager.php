<?php

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use Throwable;

class PropositionManager implements PropositionManagerInterface
{
    /**
     * @return array
     */
    public function all(): array
    {
        $propositionName = $this->getPropositionName();

        // Uses Existing Platform config structure

        return Config::get(sprintf('platform/%s', $propositionName)) ?? [];
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $propositionName = $this->getPropositionName();

        // Uses Existing Platform config structure

        return Config::get(sprintf('platform/%s.%s', $propositionName, $key));
    }

    /**
     * Get the current proposition name (runtime dynamic based on account)
     *
     * This determines which config to load from config/platform/ dynamically
     * based on the account's platformId (from API).
     *
     * @return string
     */
    public function getPropositionName(): string
    {
        $propositionName = Platform::MYPARCEL_NAME;

        try {
            $account = AccountSettings::getAccount();

            if ($account) {
                $platformId = $account->getAttribute('platformId');

                // Check for SendMyParcel (BE)
                // Note: INT-1084 changes SENDMYPARCEL_ID from 2 to 3 in the PR
                // We support both during transition period
                if (Platform::SENDMYPARCEL_ID === $platformId || 3 === $platformId) {
                    $propositionName = Platform::SENDMYPARCEL_NAME;
                }
            }
        } catch (Throwable $e) {
        // If account settings are not available, default to myparcel
    }

        return $propositionName;
    }

    public function getCarriers(): CarrierCollection
    {
        return Pdk::get('carriers');
    }
}
