<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Frontend\Settings\View\CarrierSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\CustomsSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\GeneralSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\LabelSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\OrderSettingsView;
use MyParcelNL\Pdk\Frontend\Settings\View\ProductSettingsView;

dataset('settingsViews', [
    'carrier settings'  => [CarrierSettingsView::class],
    'checkout settings' => [CheckoutSettingsView::class],
    'customs settings'  => [CustomsSettingsView::class],
    'general settings'  => [GeneralSettingsView::class],
    'label settings'    => [LabelSettingsView::class],
    'order settings'    => [OrderSettingsView::class],
    'product settings'  => [ProductSettingsView::class],
]);
