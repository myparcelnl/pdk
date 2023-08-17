<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollectionFactory;
use MyParcelNL\Pdk\Shipment\Model\DropOffDayFactory;

/**
 * @template T of DropOffPossibilities
 * @method DropOffPossibilities make()
 * @method $this withDropOffDays(DropOffDayCollectionFactory|DropOffDayCollection|DropOffDayFactory[] $dropOffDays)
 * @method $this withDropOffDaysDeviations(DropOffDayCollectionFactory|DropOffDayCollection|DropOffDayFactory[] $dropOffDaysDeviations)
 */
final class DropOffPossibilitiesFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return DropOffPossibilities::class;
    }
}
