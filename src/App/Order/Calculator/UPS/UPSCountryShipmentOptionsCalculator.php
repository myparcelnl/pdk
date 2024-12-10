<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;

class UPSCountryShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    private $countryService;

    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        $cc = $this->order->shippingAddress->cc;
        if ($this->countryService->isRow($cc) || $this->countryService->isEu($cc) || $this->countryService->isLocalCountry($cc)) {
            $this->order->deliveryOptions->date = null;
        }
    }
}
