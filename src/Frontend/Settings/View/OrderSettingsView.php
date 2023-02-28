<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Settings\View;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class OrderSettingsView extends AbstractSettingsView
{
    /**
     * @var \MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface
     */
    private $orderStatusService;

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface $orderStatusService
     */
    public function __construct(OrderStatusServiceInterface $orderStatusService)
    {
        $this->orderStatusService = $orderStatusService;
    }

    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function getElements(): FormElementCollection
    {
        $orderStatuses = $this->toSelectOptions($this->orderStatusService->all(), true);

        return new FormElementCollection([
            new InteractiveElement(
                OrderSettings::STATUS_ON_LABEL_CREATE,
                Components::INPUT_SELECT,
                ['options' => $orderStatuses]
            ),
            new InteractiveElement(
                OrderSettings::STATUS_WHEN_LABEL_SCANNED,
                Components::INPUT_SELECT,
                ['options' => $orderStatuses]
            ),
            new InteractiveElement(
                OrderSettings::STATUS_WHEN_DELIVERED,
                Components::INPUT_SELECT,
                ['options' => $orderStatuses]
            ),
            new InteractiveElement(
                OrderSettings::IGNORE_ORDER_STATUSES,
                Components::INPUT_CHECKBOX,
                ['options' => $orderStatuses]
            ),
            new InteractiveElement(OrderSettings::ORDER_STATUS_MAIL, Components::INPUT_TOGGLE),
            new InteractiveElement(OrderSettings::SEND_NOTIFICATION_AFTER, Components::INPUT_SELECT, ['options' => []]),
            new InteractiveElement(OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMP, Components::INPUT_TOGGLE),
            new InteractiveElement(OrderSettings::SAVE_CUSTOMER_ADDRESS, Components::INPUT_TOGGLE),
            new InteractiveElement(OrderSettings::EMPTY_PARCEL_WEIGHT, Components::INPUT_NUMBER),
            new InteractiveElement(OrderSettings::EMPTY_MAILBOX_WEIGHT, Components::INPUT_NUMBER),
            new InteractiveElement(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT, Components::INPUT_NUMBER),
        ]);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return OrderSettings::ID;
    }
}
