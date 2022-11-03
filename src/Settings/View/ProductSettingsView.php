<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ProductSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::ALLOW_ONLY_RECIPIENT,
                'label' => 'settings_product_allow_only_recipient',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::ALLOW_SIGNATURE,
                'label' => 'settings_product_allow_signature',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => ProductSettings::COUNTRY_OF_ORIGIN,
                'label'   => 'settings_product_country_of_origin',
                'options' => CountryService::ALL,
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::CUSTOMS_CODE,
                'label' => 'settings_product_customs_code',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::DISABLE_DELIVERY_OPTIONS,
                'label' => 'settings_product_disable_delivery_options',
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::DROP_OFF_DELAY,
                'label' => 'settings_product_drop_off_delay',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_AGE_CHECK,
                'label' => 'settings_product_export_age_check',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_INSURANCE,
                'label' => 'settings_product_export_insurance',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_LARGE_FORMAT,
                'label' => 'settings_product_export_large_format',
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::FIT_IN_MAILBOX,
                'label' => 'settings_product_fit_in_mailbox',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => ProductSettings::PACKAGE_TYPE,
                'label'   => 'settings_product_package_type',
                'options' => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME       => 'package_type_package',
                    DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => 'package_type_mailbox',
                    DeliveryOptions::PACKAGE_TYPE_LETTER_NAME        => 'package_type_letter',
                    DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => 'package_type_digital_stamp',
                ],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::RETURN_SHIPMENTS,
                'label' => 'settings_product_return_shipments',
            ],
        ]);
    }
}
