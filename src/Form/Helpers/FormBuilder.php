<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Form\Helpers;

use MyParcelNL\Pdk\Settings\Model\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Settings\Model\View\CustomsSettingsView;
use MyParcelNL\Pdk\Settings\Model\View\LabelSettingsView;
use MyParcelNL\Pdk\Settings\Model\View\OrderSettingsView;

class FormBuilder
{
    /**
     * @return \MyParcelNL\Pdk\Settings\Model\View\CustomsSettingsView
     */
    public function getCustomsSettingsView(): CustomsSettingsView
    {
        return new CustomsSettingsView();
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\View\LabelSettingsView
     */
    public function getLabelSettingsView(): LabelSettingsView
    {
        return new LabelSettingsView();
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\View\OrderSettingsView
     */
    public function getOrderSettingsView(): OrderSettingsView
    {
        return new OrderSettingsView();
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\View\CheckoutSettingsView
     */
    public function getCheckoutSettingsView(): CheckoutSettingsView
    {
        return new CheckoutSettingsView();
    }
}
