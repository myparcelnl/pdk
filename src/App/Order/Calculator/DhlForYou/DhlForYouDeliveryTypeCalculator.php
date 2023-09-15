<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Evening delivery is only allowed in the Netherlands.
 * - When evening delivery is enabled same-day delivery is not available
 */
final class DhlForYouDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
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
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (! $this->countryService->isLocalCountry($this->order->shippingAddress->cc)) {
            $this->order->deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;

            return;
        }

        if (DeliveryOptions::DELIVERY_TYPE_EVENING_NAME !== $this->order->deliveryOptions->deliveryType) {
            return;
        }

        $shipmentOptions->sameDayDelivery = TriStateService::DISABLED;
    }
}
