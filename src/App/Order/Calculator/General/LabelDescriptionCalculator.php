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

    /**
     * @return string
     */
    private function calculateDescription(): string
    {
        $description = $this->order->deliveryOptions->shipmentOptions->labelDescription
            ?? Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID)
            ?? '';

        $createString = function (string $key): string {
            return implode(', ', Utils::filterNull(Arr::pluck($this->order->lines->all(), $key)));
        };

        return preg_replace_callback_array([
            '/\[ORDER_ID\]/' => function () {
                return $this->order->externalIdentifier;
            },

            '/\[CUSTOMER_NOTE\]/' => function () {
                return $this->order->notes->firstWhere('author', OrderNote::AUTHOR_CUSTOMER)->note ?? '';
            },

            '/\[PRODUCT_ID\]/' => static function () use ($createString) {
                return $createString('product.externalIdentifier');
            },

            '/\[PRODUCT_NAME\]/' => static function () use ($createString) {
                return $createString('product.name');
            },

            '/\[PRODUCT_SKU\]/' => static function () use ($createString) {
                return $createString('product.sku');
            },

            '/\[PRODUCT_EAN\]/' => static function () use ($createString) {
                return $createString('product.ean');
            },

            '/\[PRODUCT_QTY\]/' => function () {
                return $this->order->lines->sum('quantity');
            },
        ], $description);
    }
}
