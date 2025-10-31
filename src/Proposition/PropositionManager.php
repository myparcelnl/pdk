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
     * Priority:
     * 1. Platform configured in PDK (for testing/explicit config)
     * 2. Platform from account settings platformId (runtime from API)
     * 3. Default to MyParcel
     *
     * @return string
     */
    public function getPropositionName(): string
    {
        // First check if platform is explicitly configured in PDK
        $configuredPlatform = Pdk::get('platform');
        if ($configuredPlatform) {
            return (string) $configuredPlatform;
        }

        // Otherwise, determine from account settings
        $propositionName = Platform::MYPARCEL_NAME;

        try {
            $account = AccountSettings::getAccount();

            if ($account) {
                $platformId = $account->getAttribute('platformId');

                // Check for SendMyParcel (BE)
                // Note: Platform constants use 2, but we support both 2 and 3 during transition
                if (Platform::SENDMYPARCEL_ID === $platformId || 3 === $platformId) {
                    $propositionName = Platform::SENDMYPARCEL_NAME;
                }
            }
        } catch (Throwable $e) {
            // If account settings are not available, use default
        }

        return $propositionName;
    }

    public function getCarriers(): CarrierCollection
    {
        return Pdk::get('carriers');
    }
}
