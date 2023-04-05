<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ProductSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Base\Service\CountryService
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\Pdk\Base\Service\CountryService $countryService
     */
    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        return new FormElementCollection([
            new InteractiveElement(ProductSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TRISTATE),
            new InteractiveElement(ProductSettings::EXPORT_SIGNATURE, Components::INPUT_TRISTATE),
            new InteractiveElement(
                ProductSettings::COUNTRY_OF_ORIGIN,
                Components::INPUT_SELECT,
                ['options' => $this->toSelectOptions($this->countryService->getAllTranslatable(), true)]
            ),
            new InteractiveElement(ProductSettings::CUSTOMS_CODE, Components::INPUT_TEXT),
            new InteractiveElement(ProductSettings::DISABLE_DELIVERY_OPTIONS, Components::INPUT_TRISTATE),
            new InteractiveElement(ProductSettings::DROP_OFF_DELAY, Components::INPUT_NUMBER),
            new InteractiveElement(ProductSettings::EXPORT_AGE_CHECK, Components::INPUT_TRISTATE),
            new InteractiveElement(ProductSettings::EXPORT_INSURANCE, Components::INPUT_TRISTATE),
            new InteractiveElement(ProductSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TRISTATE),
            new InteractiveElement(
                ProductSettings::PACKAGE_TYPE,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions([
                        DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME       => 'package_type_package',
                        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => 'package_type_mailbox',
                        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => 'package_type_digital_stamp',
                        DeliveryOptions::PACKAGE_TYPE_LETTER_NAME        => 'package_type_letter',
                    ]),
                ]
            ),
            new InteractiveElement(ProductSettings::FIT_IN_MAILBOX, Components::INPUT_NUMBER),
            new InteractiveElement(ProductSettings::EXPORT_RETURN, Components::INPUT_TRISTATE),
        ]);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return ProductSettings::ID;
    }
}
