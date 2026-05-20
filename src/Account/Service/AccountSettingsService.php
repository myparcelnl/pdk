<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

class AccountSettingsService implements AccountSettingsServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface
     */
    private $featuresService;

    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $pdkAccountRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $pdkAccountRepository
     * @param  \MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface   $featuresService
     */
    public function __construct(
        PdkAccountRepositoryInterface  $pdkAccountRepository,
        AccountFeaturesServiceInterface $featuresService
    ) {
        $this->pdkAccountRepository = $pdkAccountRepository;
        $this->featuresService      = $featuresService;
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getAccount(): ?Account
    {
        return $this->pdkAccountRepository->getAccount();
    }

    /**
     * Return the carriers saved for the current shop.
     *
     * Filters out carriers this PDK version does not support so a server-side
     * proposition update cannot expose an unsupported carrier to the admin UI.
     * @see Carrier::isSupported()
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getCarriers(): CarrierCollection
    {
        $shop = $this->getShop();

        if (! $shop || $shop->carriers->isEmpty()) {
            return new CarrierCollection();
        }

        // The SDK already rejects unknown V2 carriers at hydration time, leaving the
        // failed entries as raw arrays in the collection. Drop those alongside any
        // Carriers whose name our local map doesn't recognise.
        return $shop->carriers->filter(static function ($carrier): bool {
            return $carrier instanceof Carrier && Carrier::isSupported($carrier->carrier);
        })->values();
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Shop
     */
    public function getShop(): ?Shop
    {
        $account = $this->getAccount();

        return $account ? $account->shops->first() : null;
    }

    /**
     * @return bool
     */
    public function hasAccount(): bool
    {
        return null !== $this->getAccount();
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasCarrierSmallPackageContract(): bool
    {
        $account = $this->getAccount();

        return $account ? $account->generalSettings->hasCarrierSmallPackageContract : false;
    }

    /**
     * @param  string $feature
     *
     * @return bool
     */
    public function hasSubscriptionFeature(string $feature): bool
    {
        if (! $this->hasAccount()) {
            return false;
        }

        /** @var \MyParcelNL\Pdk\Base\Support\Collection $subscriptionFeatures */
        $subscriptionFeatures = $this->getAccount()->subscriptionFeatures;

        return in_array($feature, $subscriptionFeatures->toArray(), true);
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool
    {
        return $this->hasAccount()
            && (new Collection(Pdk::get('carriersWithTaxFields') ?? []))
            ->contains(function (string $carrier) {
                return $this->hasCarrier($carrier);
            });
    }

    /**
     * @return int
     */
    public function getOrderModeVersion(): int
    {
        return $this->featuresService->getOrderModeVersion();
    }

    /**
     * The order management version the PDK should behave as.
     *
     * Delegates to {@see AccountFeaturesServiceInterface::getEffectiveOrderMode()} —
     * see there for intent and future evolution. Use this (or its facade
     * {@see \MyParcelNL\Pdk\Facade\AccountSettings::getEffectiveOrderMode()}) for any
     * code path that adapts behaviour to the order management mode.
     *
     * @return int
     */
    public function getEffectiveOrderMode(): int
    {
        return $this->featuresService->getEffectiveOrderMode();
    }

    /**
     * @return bool
     */
    public function usesOrderMode(): bool
    {
        return $this->featuresService->usesOrderMode();
    }

    /**
     * @param  string $carrierName
     *
     * @return bool
     */
    protected function hasCarrier(string $carrierName): bool
    {
        return $this->hasAccount()
            && $this->getCarriers()
            ->contains(function (Carrier $carrier) use ($carrierName) {
                return $carrier->carrier === $carrierName;
            });
    }
}
