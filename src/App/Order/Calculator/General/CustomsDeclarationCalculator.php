<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;

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

        $this->generateCustomsDeclaration();
    }

    /**
     * @return void
     */
    private function generateCustomsDeclaration(): void
    {
        $this->order->customsDeclaration = new CustomsDeclaration([
            'contents' => Settings::get(CustomsSettings::PACKAGE_CONTENTS, CustomsSettings::ID),
            'invoice'  => $this->order->referenceIdentifier ?? $this->order->externalIdentifier,
            'weight'   => $this->order->physicalProperties->totalWeight ?: Pdk::get('minimumWeight'),
            'items'    => $this->order->lines
                ->onlyDeliverable()
                ->map(function (PdkOrderLine $line) {
                    return CustomsDeclarationItem::fromOrderLine($line);
                })
                ->values(),
        ]);
    }
}
