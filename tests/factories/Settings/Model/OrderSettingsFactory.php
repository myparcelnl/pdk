<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of OrderSettings
 * @method OrderSettings make()
 * @method $this withEmptyDigitalStampWeight(int $emptyDigitalStampWeight)
 * @method $this withEmptyMailboxWeight(int $emptyMailboxWeight)
 * @method $this withEmptyParcelWeight(int $emptyParcelWeight)
 * @method $this withOrderStatusMail(bool $orderStatusMail)
 * @method $this withSaveCustomerAddress(bool $saveCustomerAddress)
 * @method $this withSendNotificationAfter(string $sendNotificationAfter)
 * @method $this withSendOrderStateForDigitalStamps(bool $sendOrderStateForDigitalStamps)
 * @method $this withStatusOnLabelCreate(string $statusOnLabelCreate)
 * @method $this withStatusWhenDelivered(string $statusWhenDelivered)
 * @method $this withStatusWhenLabelScanned(string $statusWhenLabelScanned)
 */
final class OrderSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return OrderSettings::class;
    }
}
