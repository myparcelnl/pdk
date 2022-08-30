<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class GeneralSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class' => TextInput::class,
                'name'  => GeneralSettings::API_KEY,
                'label' => 'Api Key',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::ENABLE_API_LOGGING,
                'label' => 'Api logging',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::SHARE_CUSTOMER_INFORMATION,
                'label' => 'Share customer information',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::USE_SEPARATE_ADDRESS_FIELDS,
                'label' => 'Use second address field',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::CONCEPT_SHIPMENTS,
                'label' => 'Turn on concept shipments',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::ORDER_MODE,
                'label' => 'Turn on order management',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => GeneralSettings::PRICE_TYPE,
                'label'   => 'Price type display',
                'options' => [],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::TRACK_TRACE_EMAIL,
                'label' => 'Track & trace in email',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::TRACK_TRACE_MY_ACCOUNT,
                'label' => 'Track & Trace in my account',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::SHOW_DELIVERY_DAY,
                'label' => 'Show delivery day to customer',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::BARCODE_IN_NOTE,
                'label' => 'Save barcode in note',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::PROCESS_DIRECTLY,
                'label' => 'Process orders automatically',
            ],
        ]);
    }
}
