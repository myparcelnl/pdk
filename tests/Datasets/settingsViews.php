<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Frontend\View\CarrierSettingsView;
use MyParcelNL\Pdk\Frontend\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Frontend\View\CustomsSettingsView;
use MyParcelNL\Pdk\Frontend\View\LabelSettingsView;
use MyParcelNL\Pdk\Frontend\View\OrderSettingsView;
use MyParcelNL\Pdk\Frontend\View\PrinterGroupIdView;
use MyParcelNL\Pdk\Frontend\View\PrintOptionsView;
use MyParcelNL\Pdk\Frontend\View\ProductSettingsView;

dataset('settingsViews', [
    'carrier settings'  => [CarrierSettingsView::class],
    'checkout settings' => [CheckoutSettingsView::class],
    'customs settings'  => [CustomsSettingsView::class],
    'label settings'    => [LabelSettingsView::class],
    'order settings'    => [OrderSettingsView::class],
    'printer group id'  => [PrinterGroupIdView::class],
    'print options'     => [PrintOptionsView::class],
    'product settings'  => [ProductSettingsView::class],
]);
