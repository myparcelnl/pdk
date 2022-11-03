<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CustomsSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class'   => SelectInput::class,
                'name'    => CustomsSettings::PACKAGE_CONTENTS,
                'label'   => 'settings_customs_package_contents',
                'options' => CustomsSettings::PACKAGE_CONTENTS_LIST,
            ],
            [
                'class' => TextInput::class,
                'name'  => CustomsSettings::CUSTOMS_CODE,
                'label' => 'settings_customs_customs_code',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => CustomsSettings::COUNTRY_OF_ORIGIN,
                'label'   => 'settings_customs_country_of_origin',
                'options' => CountryService::ALL,
            ],
        ]);
    }
}
