<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Support\Str;

final class LabelDescriptionCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $description = $this->calculateDescription();

        $this->order->deliveryOptions->shipmentOptions->labelDescription = $description;
    }

    /**
     * @return string
     */
    private function calculateDescription(): string
    {
        $description = $this->getDescription();

        $createString = function (string $key): string {
            return implode(', ', Utils::filterNull(Arr::pluck($this->order->lines->all(), $key)));
        };

        $labelDescription = preg_replace_callback_array([
            '/\[ORDER_ID\]/' => function () {
                return $this->order->referenceIdentifier;
            },

            '/\[CUSTOMER_NOTE\]/' => function () {
                return $this->order->notes->firstWhere('author', OrderNote::AUTHOR_CUSTOMER)->note ?? '';
            },

            '/\[DELIVERY_DATE\]/' => function () {
                return $this->order->deliveryOptions->date
                    ? $this->order->deliveryOptions->date->format('Y-m-d')
                    : '';
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

        return Str::limit($labelDescription, Pdk::get('labelDescriptionMaxLength'));
    }

    /**
     * @return string
     */
    private function getDescription(): string
    {
        $labelDescriptionFromOrder = $this->order->deliveryOptions->shipmentOptions->labelDescription;

        if (is_string($labelDescriptionFromOrder) && $labelDescriptionFromOrder !== (string) TriStateService::INHERIT) {
            return $labelDescriptionFromOrder;
        }

        return Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID) ?? '';
    }
}
