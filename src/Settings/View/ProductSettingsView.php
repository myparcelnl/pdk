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
                'label' => 'Allow only recipient',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::ALLOW_SIGNATURE,
                'label' => 'Allow signature',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => ProductSettings::COUNTRY_OF_ORIGIN,
                'label'   => 'Country of origin',
                'options' => CountryService::ALL,
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::CUSTOMS_CODE,
                'label' => 'Customs code',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::DISABLE_DELIVERY_OPTIONS,
                'label' => 'Disable delivery options',
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::DROP_OFF_DELAY,
                'label' => 'Drop-off delay',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_AGE_CHECK,
                'label' => 'Export age check',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_INSURANCE,
                'label' => 'Export insurance',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::EXPORT_LARGE_FORMAT,
                'label' => 'Export large format',
            ],
            [
                'class' => TextInput::class,
                'name'  => ProductSettings::FIT_IN_MAILBOX,
                'label' => 'Fit in mailbox',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => ProductSettings::PACKAGE_TYPE,
                'label'   => 'Package type',
                'options' => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME       => 'Package',
                    DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => 'Mailbox',
                    DeliveryOptions::PACKAGE_TYPE_LETTER_NAME        => 'Letter',
                    DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => 'Digital stamp',
                ],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => ProductSettings::RETURN_SHIPMENTS,
                'label' => 'Return shipments',
            ],
        ]);
    }
}
