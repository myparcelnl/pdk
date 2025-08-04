<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * @deprecated Use PropositionService instead.
 * @see \MyParcelNL\Pdk\Proposition\Service\PropositionService
 * @package MyParcelNL\Pdk\Platform
 */
class PlatformManager implements PlatformManagerInterface
{
    protected PropositionService $propositionService;

    public function __construct(PropositionService $propositionService)
    {
        $this->propositionService = $propositionService;
    }
    /**
     * @return array
     */
    public function all(): array
    {
        return $this->propositionService->mapToPlatformConfig(
            $this->propositionService->getPropositionConfig()
        );
    }

    /**
     * @param  string $key
     *
     * @return mixed
     * @deprecated This function will be removed in the future. You should use the PropositionService to get specific configuration values.
     * @see PropositionService::getPropositionConfig()
    */
    public function get(string $key)
    {
        $config = $this->all();
        return $config[$key] ?? null;
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     * @deprecated Use PropositionService::getCarriers() instead.
     * @see PropositionService::getCarriers()
     */
    public function getCarriers(): CarrierCollection
    {
        return $this->propositionService->getCarriers(true);
    }

    /**
     * @return string
     * @deprecated
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
