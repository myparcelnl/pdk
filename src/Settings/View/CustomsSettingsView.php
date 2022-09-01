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
                'name'    => CustomsSettings::DEFAULT_PACKAGE_CONTENTS,
                'label'   => 'Package contents',
                'options' => CustomsSettings::PACKAGE_CONTENTS_LIST,
            ],
            [
                'class' => TextInput::class,
                'name'  => CustomsSettings::DEFAULT_CUSTOMS_CODE,
                'label' => 'Default customs code',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => CustomsSettings::DEFAULT_COUNTRY_OF_ORIGIN,
                'label'   => 'Default country of origin',
                'options' => CountryService::ALL,
            ],
        ]);
    }
}
