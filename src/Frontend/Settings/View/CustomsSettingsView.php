<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CustomsSettingsView extends AbstractSettingsView
{
    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        return new FormElementCollection([
            new InteractiveElement(
                CustomsSettings::PACKAGE_CONTENTS,
                Components::INPUT_SELECT,
                ['options' => $this->toSelectOptions(CustomsSettings::PACKAGE_CONTENTS_LIST)]
            ),
            new InteractiveElement(CustomsSettings::CUSTOMS_CODE, Components::INPUT_TEXT),
            new InteractiveElement(
                CustomsSettings::COUNTRY_OF_ORIGIN,
                Components::INPUT_SELECT,
                ['options' => $this->toSelectOptions(CountryService::ALL)]
            ),
        ]);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return CustomsSettings::ID;
    }
}
