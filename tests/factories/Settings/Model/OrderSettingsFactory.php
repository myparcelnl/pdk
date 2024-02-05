<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of OrderSettings
 * @method OrderSettings make()
 * @method $this withBarcodeInNote(bool $barcodeInNote);
 * @method $this withBarcodeInNoteTitle(null|string $barcodeInNoteTitle);
 * @method $this withConceptShipments(bool $conceptShipments);
 * @method $this withEmptyDigitalStampWeight(int $emptyDigitalStampWeight)
 * @method $this withEmptyMailboxWeight(int $emptyMailboxWeight)
 * @method $this withEmptyParcelWeight(int $emptyParcelWeight)
 * @method $this withEmptyPackageSmallWeight(int $emptyPackageSmallWeight));
 * @method $this withExportWithAutomaticStatus(null|string $exportWithAutomaticStatus);
 * @method $this withOrderMode(bool $orderMode);
 * @method $this withOrderStatusMail(bool $orderStatusMail)
 * @method $this withProcessDirectly(string $processDirectly);
 * @method $this withSaveCustomerAddress(bool $saveCustomerAddress)
 * @method $this withSendNotificationAfter(string $sendNotificationAfter)
 * @method $this withSendReturnEmail(bool $sendReturnEmail);
 * @method $this withShareCustomerInformation(bool $shareCustomerInformation);
 * @method $this withStatusOnLabelCreate(string $statusOnLabelCreate)
 * @method $this withStatusWhenDelivered(string $statusWhenDelivered)
 * @method $this withStatusWhenLabelScanned(string $statusWhenLabelScanned)
 * @method $this withTrackTraceInAccount(bool $trackTraceInAccount);
 * @method $this withTrackTraceInEmail(bool $trackTraceInEmail);
 *
 */
final class OrderSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return OrderSettings::class;
    }
}
