<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\NumberInput;
use MyParcelNL\Pdk\Frontend\Form\Element\SelectInput;
use MyParcelNL\Pdk\Frontend\Form\Element\SettingsDivider;
use MyParcelNL\Pdk\Frontend\Form\Element\TextInput;
use MyParcelNL\Pdk\Frontend\Form\Element\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
final class OrderSettingsView extends NewAbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface
     */
    private $orderStatusService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface $orderStatusService
     */
    public function __construct(OrderStatusServiceInterface $orderStatusService)
    {
        parent::__construct();
        $this->orderStatusService = $orderStatusService;
    }

    protected function addElements(): void
    {
        $orderStatusOptions      = $this->orderStatusService->all();
        $orderStatusOptionsFlags = ElementBuilderWithOptionsInterface::ADD_NONE | ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL;

        $this->formBuilder->add(
            new SettingsDivider($this->label('general')),

            $this->createOrderModeToggle(),
            (new ToggleInput(OrderSettings::CONCEPT_SHIPMENTS))->visibleWhen(OrderSettings::ORDER_MODE, false),
            new ToggleInput(OrderSettings::PROCESS_DIRECTLY),
            new ToggleInput(OrderSettings::SEND_RETURN_EMAIL),
            new ToggleInput(OrderSettings::SAVE_CUSTOMER_ADDRESS),
            new ToggleInput(OrderSettings::SHARE_CUSTOMER_INFORMATION),

            new SettingsDivider($this->label('status')),

            (new SelectInput(OrderSettings::STATUS_ON_LABEL_CREATE))
                ->withOptions($orderStatusOptions, $orderStatusOptionsFlags),

            (new SelectInput(OrderSettings::STATUS_WHEN_LABEL_SCANNED))
                ->withOptions($orderStatusOptions, $orderStatusOptionsFlags),

            (new SelectInput(OrderSettings::STATUS_WHEN_DELIVERED))
                ->withOptions($orderStatusOptions, $orderStatusOptionsFlags),

            (new SelectInput(OrderSettings::SEND_NOTIFICATION_AFTER))
                ->withOptions($orderStatusOptions, $orderStatusOptionsFlags),

            new SettingsDivider($this->label('track_trace')),

            new ToggleInput(OrderSettings::TRACK_TRACE_IN_EMAIL),
            new ToggleInput(OrderSettings::TRACK_TRACE_IN_ACCOUNT),

            new SettingsDivider($this->label('weight')),

            (new NumberInput(OrderSettings::EMPTY_PARCEL_WEIGHT))->withProp('min', 0),
            (new NumberInput(OrderSettings::EMPTY_MAILBOX_WEIGHT))->withProp('min', 0),
            (new NumberInput(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT))->withProp('min', 0),

            new SettingsDivider($this->label('order_notes')),

            new ToggleInput(OrderSettings::BARCODE_IN_NOTE),
            (new TextInput(OrderSettings::BARCODE_IN_NOTE_TITLE))->visibleWhen(OrderSettings::BARCODE_IN_NOTE)
        );
    }

    protected function getPrefix(): string
    {
        return OrderSettings::ID;
    }

    /**
     * The toggle should be readonly and show a hint if the account does not have order mode enabled.
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Element\Contract\ElementBuilderInterface|\MyParcelNL\Pdk\Frontend\Form\Element\ToggleInput
     */
    private function createOrderModeToggle()
    {
        $usesOrderMode = AccountSettings::usesOrderMode();

        $orderModeToggle = (new ToggleInput(OrderSettings::ORDER_MODE))
            ->withProps(
                $usesOrderMode
                    ? []
                    : ['subtext' => 'hint_enable_order_mode_backoffice']
            );

        if (! $usesOrderMode) {
            $orderModeToggle->readOnlyWhen();
        }

        return $orderModeToggle;
    }
}
