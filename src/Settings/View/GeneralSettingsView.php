<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
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
                'label' => 'settings_general_api_key',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::API_LOGGING,
                'label' => 'settings_general_api_logging',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::SHARE_CUSTOMER_INFORMATION,
                'label' => 'settings_general_share_customer_information',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::CONCEPT_SHIPMENTS,
                'label' => 'settings_general_concept_shipments',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::ORDER_MODE,
                'label' => 'settings_general_order_mode',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::TRACK_TRACE_IN_EMAIL,
                'label' => 'settings_general_track_trace_in_email',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::TRACK_TRACE_IN_ACCOUNT,
                'label' => 'settings_general_track_trace_in_account',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::BARCODE_IN_NOTE,
                'label' => 'settings_general_barcode_in_note',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => GeneralSettings::PROCESS_DIRECTLY,
                'label' => 'settings_general_process_directly',
            ],
        ]);
    }
}
