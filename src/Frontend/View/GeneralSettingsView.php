<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class GeneralSettingsView extends AbstractSettingsView
{
    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        return [
            (new InteractiveElement(
                GeneralSettings::ORDER_MODE,
                Components::INPUT_TOGGLE,
                AccountSettings::usesOrderMode()
                    ? []
                    : [
                    'subtext' => 'hint_enable_order_mode_backoffice',
                ]
            ))->builder(function (FormOperationBuilder $builder) {
                if (AccountSettings::usesOrderMode()) {
                    return;
                }

                $builder->readOnlyWhen(GeneralSettings::ORDER_MODE, false);
            }),

            (new InteractiveElement(GeneralSettings::CONCEPT_SHIPMENTS, Components::INPUT_TOGGLE))->builder(
                function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(GeneralSettings::ORDER_MODE);
                }
            ),

            new InteractiveElement(GeneralSettings::PROCESS_DIRECTLY, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::SHARE_CUSTOMER_INFORMATION, Components::INPUT_TOGGLE),

            new SettingsDivider($this->createLabel($this->getLabelPrefix(), 'track_trace')),

            new InteractiveElement(GeneralSettings::TRACK_TRACE_IN_EMAIL, Components::INPUT_TOGGLE),
            new InteractiveElement(GeneralSettings::TRACK_TRACE_IN_ACCOUNT, Components::INPUT_TOGGLE),

            new SettingsDivider($this->createLabel($this->getLabelPrefix(), 'order_notes')),

            new InteractiveElement(GeneralSettings::BARCODE_IN_NOTE, Components::INPUT_TOGGLE),
            $this->withOperation(
                function (FormOperationBuilder $builder) {
                    $builder->visibleWhen(GeneralSettings::BARCODE_IN_NOTE);
                },
                new InteractiveElement(GeneralSettings::BARCODE_IN_NOTE_TITLE, Components::INPUT_TEXT)
            ),
        ];
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return GeneralSettings::ID;
    }
}
