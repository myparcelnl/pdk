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

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    private function isInternationalMailbox(Carrier $carrier): bool
    {
        $carrierSettings = Settings::all()->carrier->get($carrier->externalIdentifier);

        $isMailbox         = $this->order->deliveryOptions->packageType === DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME;
        $isNotLocal        = ! $this->countryService->isLocalCountry($this->order->shippingAddress->cc);
        $enabledInSettings = $carrierSettings->allowInternationalMailbox;

        return $isMailbox && $isNotLocal && $enabledInSettings;
    }
}
