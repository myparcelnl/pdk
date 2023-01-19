<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class GeneralSettingsView extends AbstractSettingsView
{
    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        return new FormElementCollection([
            new InteractiveElement(GeneralSettings::API_LOGGING, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::SHARE_CUSTOMER_INFORMATION, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::CONCEPT_SHIPMENTS, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::ORDER_MODE, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::TRACK_TRACE_IN_EMAIL, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::TRACK_TRACE_IN_ACCOUNT, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::BARCODE_IN_NOTE, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::PROCESS_DIRECTLY, Components::INPUT_TOGGLE),
        ]);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return GeneralSettings::ID;
    }
}
