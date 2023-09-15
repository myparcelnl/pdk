<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;

class AccountSettingsService implements AccountSettingsServiceInterface
{
    public function __construct(private readonly PdkAccountRepositoryInterface $pdkAccountRepository)
    {
    }

    public function getAccount(): ?Account
    {
        return $this->pdkAccountRepository->getAccount();
    }

    /**
     * @deprecated use getCarriers()
     */
    public function getCarrierOptions(): CarrierCollection
    {
        return $this->getCarriers();
    }

    public function getCarriers(): CarrierCollection
    {
        $shop = $this->getShop();

        if (! $shop || $shop->carriers->isEmpty()) {
            return new CarrierCollection();
        }

        $allowedCarriers = Platform::getCarriers();

        return $shop->carriers
            ->filter(function (Carrier $carrier) use ($allowedCarriers) {
                $isAllowed = $allowedCarriers->contains('name', $carrier->name);

                return $isAllowed && $carrier->enabled && $carrier->capabilities;
            })
            ->sort(function (Carrier $carrierA, Carrier $carrierB) use ($allowedCarriers) {
                $aIndex = $allowedCarriers->search(
                    fn(Carrier $allowedCarrier) => $allowedCarrier->name === $carrierA->name,
                    true
                );

                $bIndex = $allowedCarriers->search(
                    fn(Carrier $allowedCarrier) => $allowedCarrier->name === $carrierB->name,
                    true
                );

                return $aIndex === $bIndex
                    ? $carrierA->subscriptionId <=> $carrierB->subscriptionId
                    : $aIndex <=> $bIndex;
            })
            ->values();
    }

    public function getShop(): ?Shop
    {
        $account = $this->getAccount();

        return $account ? $account->shops->first() : null;
    }

    public function hasAccount(): bool
    {
        return null !== $this->getAccount();
    }

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
     * @noinspection PhpUnused
     */
    public function hasTaxFields(): bool
    {
        return $this->hasAccount()
            && (new Collection(Pdk::get('carriersWithTaxFields') ?? []))
                ->contains(fn(string $carrier) => $this->hasCarrier($carrier));
    }

    public function usesOrderMode(): bool
    {
        return $this->getAccount()->generalSettings->orderMode;
    }

    protected function hasCarrier(string $carrierName): bool
    {
        return $this->hasAccount()
            && $this->getCarriers()
                ->contains(fn(Carrier $carrier) => $carrier->name === $carrierName);
    }
}
