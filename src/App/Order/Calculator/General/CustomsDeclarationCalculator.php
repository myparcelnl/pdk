<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;

final class CustomsDeclarationCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);
        $cc             = $this->order->shippingAddress->cc;

        if (! $cc || ! $countryService->isRow($cc)) {
            $this->order->customsDeclaration = null;

            return;
        }

        if ($this->order->customsDeclaration) {
            return;
        }

        $this->order->customsDeclaration = CustomsDeclaration::fromPdkOrder($this->order);
    }
}
