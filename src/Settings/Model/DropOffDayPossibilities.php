<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use DateTimeImmutable;
use DateTimeInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Settings;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

/**
 * @property DropOffDayCollection $dropOffDays
 * @property DropOffDayCollection $dropOffDaysDeviations
 * @property null|int             $dropOffDelay
 * @property null|int             $deliveryDaysWindow
 */
class DropOffDayPossibilities extends Model
{
    protected $attributes = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
        'dropOffDelay'          => null,
        'deliveryDaysWindow'    => null,
    ];

    protected $casts      = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
        'dropOffDelay'          => 'int',
        'deliveryDaysWindow'    => 'int',
    ];

    public function __construct()
    {
        parent::__construct(Settings::get('delivery.dropOffDayPossibilities'));
    }

    /**
     * @param  null|\DateTimeImmutable $date
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getPossibleDropOffDays(DateTimeImmutable $date = null): DropOffDayCollection
    {
        $fromDate     = $this->createFromDate($date);
        $deviatedDays = $this->getRelevantDeviatedDropOffDays($fromDate);

        $newDropOffDays = [];
        $day            = 0;
        $items          = 0;

        do {
            $dropOffDate = $fromDate->modify("+$day day");
            $weekday     = (int) $dropOffDate->format('w');

            /** @var DropOffDay $matchingDay */
            $matchingDay = $this->dropOffDays->firstWhere('weekday', $weekday);
            $deviation   = $deviatedDays->firstWhere('date', '==', $dropOffDate);

            $matchingDayArray = Utils::mergeArraysWithoutNull(
                $matchingDay ? $matchingDay->toArray() : [],
                $deviation ? $deviation->toArray() : []
            );

            if ($matchingDayArray['dispatch']) {
                $newDropOffDays[] = ['date' => $dropOffDate, 'weekday' => $weekday] + $matchingDayArray;
                $items++;
            }

            $day++;
        } while ($items < $this->deliveryDaysWindow);

        return new DropOffDayCollection($newDropOffDays);
    }

    /**
     * @param  null|\DateTimeImmutable $date
     *
     * @return \DateTimeImmutable|false
     */
    private function createFromDate(?DateTimeImmutable $date)
    {
        $fromDate = (new DateTimeImmutable('today'))->modify("+{$this->dropOffDelay} day");

        if ($date) {
            $newDate  = $date->setTime(0, 0);
            $fromDate = $newDate ?: $fromDate;
        }

        return $fromDate;
    }

    /**
     * @param  \DateTimeInterface $minDate
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection
     */
    private function getRelevantDeviatedDropOffDays(DateTimeInterface $minDate): DropOffDayCollection
    {
        return $this->dropOffDaysDeviations
            ->where('date', '>=', $minDate)
            ->where('date', '<=', $minDate->modify('+1 year'));
    }
}
