<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\View\CarrierSettingsView;
use MyParcelNL\Pdk\Frontend\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Frontend\View\CustomsSettingsView;
use MyParcelNL\Pdk\Frontend\View\LabelSettingsView;
use MyParcelNL\Pdk\Frontend\View\OrderSettingsView;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

/**
 * @property array{name: string, label: string, type: string}[]   $order
 * @property array{name: string, label: string, type: string}[]   $label
 * @property array{name: string, label: string, type: string}[]   $customs
 * @property array{name: string, label: string, type: string}[]   $checkout
 * @property array{name: string, label: string, type: string}[][] $carrier
 */
class PluginSettingsViewContext implements Arrayable
{
    private const ID_VIEW_MAP = [
        OrderSettings::ID    => OrderSettingsView::class,
        LabelSettings::ID    => LabelSettingsView::class,
        CustomsSettings::ID  => CustomsSettingsView::class,
        CheckoutSettings::ID => CheckoutSettingsView::class,
        CarrierSettings::ID  => CarrierSettingsView::class,
    ];

    private $views = [];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! AccountSettings::getAccount()) {
            return;
        }

        foreach (self::ID_VIEW_MAP as $id => $viewClass) {
            /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
            $view = Pdk::get($viewClass);

            $this->views[$id] = $view->toArray();
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->views;
    }
}
