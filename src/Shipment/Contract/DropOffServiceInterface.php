<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Contract;

use DateTimeImmutable;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

interface DropOffServiceInterface
{
    public function getForDate(CarrierSettings $settings, DateTimeImmutable $date = null): ?DropOffDay;

    public function getPossibleDropOffDays(
        CarrierSettings   $settings,
        DateTimeImmutable $date = null
    ): DropOffDayCollection;
}
