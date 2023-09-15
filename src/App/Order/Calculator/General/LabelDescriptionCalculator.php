<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

final class LabelDescriptionCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $this->order->deliveryOptions->shipmentOptions->labelDescription = $this->calculateDescription();
    }

    private function calculateDescription(): string
    {
        $description = $this->order->deliveryOptions->shipmentOptions->labelDescription
            ?? Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID)
            ?? '';

        $createString = fn(string $key): string => implode(
            ', ',
            Utils::filterNull(Arr::pluck($this->order->lines->all(), $key))
        );

        return preg_replace_callback_array([
            '/\[ORDER_ID\]/' => fn() => $this->order->externalIdentifier,

            '/\[CUSTOMER_NOTE\]/' => fn() => $this->order->notes->firstWhere(
                'author',
                OrderNote::AUTHOR_CUSTOMER
            )->note ?? '',

            '/\[PRODUCT_ID\]/' => static fn() => $createString('product.externalIdentifier'),

            '/\[PRODUCT_NAME\]/' => static fn() => $createString('product.name'),

            '/\[PRODUCT_SKU\]/' => static fn() => $createString('product.sku'),

            '/\[PRODUCT_EAN\]/' => static fn() => $createString('product.ean'),

            '/\[PRODUCT_QTY\]/' => fn() => $this->order->lines->sum('quantity'),
        ], (string) $description);
    }
}
