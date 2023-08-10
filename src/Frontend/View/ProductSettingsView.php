<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ProductSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService
     */
    public function __construct(CountryServiceInterface $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        return [
            /**
             * MyParcel
             */
            new SettingsDivider($this->getSettingKey('myparcel_options')),
            new InteractiveElement(
                ProductSettings::PACKAGE_TYPE,
                Components::INPUT_SELECT,
                ['options' => $this->createPackageTypeOptions()]
            ),

            new InteractiveElement(ProductSettings::FIT_IN_MAILBOX, Components::INPUT_NUMBER, ['min' => -1]),

            /**
             * Delivery options.
             */
            new SettingsDivider($this->getSettingKey('delivery_options')),
            new InteractiveElement(
                ProductSettings::DROP_OFF_DELAY,
                Components::INPUT_NUMBER,
                [
                    '$attributes' => [
                        'min' => Pdk::get('dropOffDelayMinimum'),
                        'max' => Pdk::get('dropOffDelayMaximum'),
                    ],
                ]
            ),
            new InteractiveElement(ProductSettings::DISABLE_DELIVERY_OPTIONS, Components::INPUT_TOGGLE),

            /**
             * Customs options.
             */
            new SettingsDivider($this->getSettingKey('customs_options')),
            new InteractiveElement(
                ProductSettings::COUNTRY_OF_ORIGIN,
                Components::INPUT_SELECT,
                [
                    'options' => $this->toSelectOptions(
                        $this->countryService->getAllTranslatable(),
                        AbstractSettingsView::SELECT_INCLUDE_OPTION_NONE
                    ),
                ]
            ),
            new InteractiveElement(
                ProductSettings::CUSTOMS_CODE,
                Components::INPUT_TEXT,
                ['$attributes' => ['maxlength' => Pdk::get('customsCodeMaxLength')]]
            ),

            /**
             * Export options.
             */
            new SettingsDivider($this->getSettingKey('export_options')),
            new InteractiveElement(ProductSettings::EXPORT_AGE_CHECK, Components::INPUT_TRI_STATE),
            new InteractiveElement(ProductSettings::EXPORT_INSURANCE, Components::INPUT_TRI_STATE),
            new InteractiveElement(ProductSettings::EXPORT_LARGE_FORMAT, Components::INPUT_TRI_STATE),
            new InteractiveElement(ProductSettings::EXPORT_ONLY_RECIPIENT, Components::INPUT_TRI_STATE),
            new InteractiveElement(ProductSettings::EXPORT_SIGNATURE, Components::INPUT_TRI_STATE),
            new InteractiveElement(ProductSettings::EXPORT_RETURN, Components::INPUT_TRI_STATE),
        ];
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return ProductSettings::ID;
    }
}
