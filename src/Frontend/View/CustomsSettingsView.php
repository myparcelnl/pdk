<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CustomsSettingsView extends AbstractSettingsView
{
    public function __construct(private readonly CountryServiceInterface $countryService)
    {
    }

    protected function createElements(): ?array
    {
        return [
            new InteractiveElement(
                CustomsSettings::PACKAGE_CONTENTS,
                Components::INPUT_SELECT,
                ['options' => $this->toSelectOptions(CustomsSettings::PACKAGE_CONTENTS_LIST)]
            ),
            new InteractiveElement(CustomsSettings::CUSTOMS_CODE, Components::INPUT_TEXT),

            new InteractiveElement(
                CustomsSettings::COUNTRY_OF_ORIGIN,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions(
                        $this->countryService->getAllTranslatable(),
                        AbstractSettingsView::SELECT_INCLUDE_OPTION_NONE
                    ),
                    'sort'    => ElementBuilderWithOptionsInterface::SORT_ASC_VALUE,
                ]
            ),
        ];
    }

    protected function getSettingsId(): string
    {
        return CustomsSettings::ID;
    }
}
