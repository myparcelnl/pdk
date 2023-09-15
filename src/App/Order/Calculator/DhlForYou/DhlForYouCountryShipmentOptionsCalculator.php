<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class DhlForYouCountryShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
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
        if (! $this->countryService->isRow($this->order->shippingAddress->cc)) {
            return;
        }

        $this->order->deliveryOptions->shipmentOptions->sameDayDelivery = TriStateService::DISABLED;
    }
}
