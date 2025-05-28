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
 * - Only recipient is disabled when pickup is selected
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
        $deliveryOptions = $this->order->deliveryOptions;
        $cc              = $this->order->shippingAddress->cc;

        switch ($deliveryOptions->deliveryType) {
            case DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME:
                $deliveryOptions->shipmentOptions->onlyRecipient = TriStateService::DISABLED;
                break;
            case DeliveryOptions::DELIVERY_TYPE_EVENING_NAME:
                if (! $this->countryService->isLocalCountry($cc)) {
                    $this->order->deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
                    break;
                }
                $deliveryOptions->shipmentOptions->sameDayDelivery = TriStateService::DISABLED;
                break;
            default:
                break;
        }
    }
}
