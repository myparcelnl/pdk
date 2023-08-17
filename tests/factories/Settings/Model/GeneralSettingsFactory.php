<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of GeneralSettings
 * @method GeneralSettings make()
 * @method $this withBarcodeInNote(bool $barcodeInNote)
 * @method $this withBarcodeInNoteTitle(string $barcodeInNoteTitle)
 * @method $this withConceptShipments(bool $conceptShipments)
 * @method $this withExportWithAutomaticStatus(string $exportWithAutomaticStatus)
 * @method $this withOrderMode(bool $orderMode)
 * @method $this withProcessDirectly(bool $processDirectly)
 * @method $this withShareCustomerInformation(bool $shareCustomerInformation)
 * @method $this withTrackTraceInAccount(bool $trackTraceInAccount)
 * @method $this withTrackTraceInEmail(bool $trackTraceInEmail)
 */
final class GeneralSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return GeneralSettings::class;
    }
}
