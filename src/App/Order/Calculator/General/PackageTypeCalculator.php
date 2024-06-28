<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class PackageTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        // All package types are allowed when shipping within the local country.
        if ($this->countryService->isLocalCountry($this->order->shippingAddress->cc)) {
            return;
        }

        // Letters are allowed outside the local country as well.
        if (DeliveryOptions::PACKAGE_TYPE_LETTER_NAME === $this->order->deliveryOptions->packageType) {
            return;
        }

        // Small packages are allowed outside the local country as well.
        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME === $this->order->deliveryOptions->packageType) {
            return;
        }

        $carrier = $this->order->deliveryOptions->carrier;

        if ($this->isInternationalMailbox($carrier)) {
            return;
        }

        $this->order->deliveryOptions->packageType = DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME;
    }

    private function isAddressInternational(string $cc): bool
    {
        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);
        return $countryService->isInternational($cc);
    }

    private function isCarrierInternationalMailboxOn(Carrier $carrier): bool
    {
        $settings = Settings::all()->carrier->get($carrier->externalIdentifier);
        if ($settings->allowInternationalMailbox) {
            return true;
        }

        return false;
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    private function isInternationalMailbox(Carrier $carrier): bool
    {
        if ($this->order->deliveryOptions->packageType !== DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME) {
            return false;
        }

        $cc = $this->order->shippingAddress->cc;
        if (! $this->isAddressInternational($cc)) {
            return false;
        }

        $hasCarrierMailContract = AccountSettings::hasCarrierMailContract();
        if (! $hasCarrierMailContract) {
            return false;
        }

        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);
        $schema->setCarrier($carrier);

        if (! $schema->canHaveCarrierMailContract()) {
            return false;
        }

        return $this->isCarrierInternationalMailboxOn($carrier);
    }
}
