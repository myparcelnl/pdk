<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Form\SettingsDivider;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class OrderSettingsView extends AbstractSettingsView
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
        $this->orderStatusService = $orderStatusService;
    }

    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        $orderStatuses         = $this->toSelectOptions(
            $this->orderStatusService->all(),
            AbstractSettingsView::SELECT_USE_PLAIN_LABEL
        );
        $orderStatusesWithNone = $this->addDefaultOption(
            $orderStatuses,
            AbstractSettingsView::SELECT_INCLUDE_OPTION_NONE
        );

        return [
            new InteractiveElement(OrderSettings::SAVE_CUSTOMER_ADDRESS, Components::INPUT_TOGGLE),

            new SettingsDivider($this->createLabel($this->getLabelPrefix(), 'status')),

            new InteractiveElement(
                OrderSettings::STATUS_ON_LABEL_CREATE,
                Components::INPUT_SELECT,
                ['options' => $orderStatusesWithNone]
            ),
            new InteractiveElement(
                OrderSettings::STATUS_WHEN_LABEL_SCANNED,
                Components::INPUT_SELECT,
                ['options' => $orderStatusesWithNone]
            ),
            new InteractiveElement(
                OrderSettings::STATUS_WHEN_DELIVERED,
                Components::INPUT_SELECT,
                ['options' => $orderStatusesWithNone]
            ),
            new InteractiveElement(OrderSettings::ORDER_STATUS_MAIL, Components::INPUT_TOGGLE),
            (new InteractiveElement(
                OrderSettings::SEND_NOTIFICATION_AFTER,
                Components::INPUT_SELECT,
                [
                    'options' => $orderStatusesWithNone,
                ]
            ))->builder(function (FormOperationBuilder $builder) {
                $builder->visibleWhen(OrderSettings::ORDER_STATUS_MAIL);
            }),

            new InteractiveElement(
                OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMP,
                Components::INPUT_SELECT,
                [
                    'options' => $orderStatusesWithNone,
                ]
            ),

            new SettingsDivider($this->createLabel($this->getLabelPrefix(), 'weight')),

            new InteractiveElement(OrderSettings::EMPTY_PARCEL_WEIGHT, Components::INPUT_NUMBER),
            new InteractiveElement(OrderSettings::EMPTY_MAILBOX_WEIGHT, Components::INPUT_NUMBER),
            new InteractiveElement(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT, Components::INPUT_NUMBER),
        ];
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return OrderSettings::ID;
    }
}
