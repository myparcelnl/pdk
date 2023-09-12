<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class CustomsDeclarationCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var mixed|\MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        $cc = $this->order->shippingAddress->cc;

        if (! $this->countryService->isRow($cc)) {
            return;
        }

        $countryOfOrigin = Settings::get(CustomsSettings::COUNTRY_OF_ORIGIN, CustomsSettings::ID);
        $customsCode     = Settings::get(CustomsSettings::CUSTOMS_CODE, CustomsSettings::ID);

        $this->order->customsDeclaration->items->each(function ($item) use ($countryOfOrigin, $customsCode) {
            if (TristateService::INHERIT === (int) $item->country) {
                $item->country = $countryOfOrigin;
            }
            if (TristateService::INHERIT === (int) $item->classification) {
                $item->classification = $customsCode;
            }
        });

        $__AAAAA = $this->order->customsDeclaration->items[0];
        $__BBBBB = clone($__AAAAA);
    }
}
