<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

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
        if ($this->countryService->isLocalCountry($this->order->shippingAddress->cc)) {
            return;
        }

        if (DeliveryOptions::PACKAGE_TYPE_LETTER_NAME === $this->order->deliveryOptions->packageType) {
            return;
        }

        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME === $this->order->deliveryOptions->packageType) {
            return;
        }

        $this->order->deliveryOptions->packageType = DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME;
    }
}
