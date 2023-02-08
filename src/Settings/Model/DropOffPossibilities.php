<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use DateTimeImmutable;
use DateTimeInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

/**
 * @property DropOffDayCollection $dropOffDays
 * @property DropOffDayCollection $dropOffDaysDeviations
 * @property int                  $dropOffDelay
 * @property int                  $deliveryDaysWindow
 */
class DropOffPossibilities extends Model
{
    protected $attributes = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
        'dropOffDelay'          => 1,
        'deliveryDaysWindow'    => 7,
    ];

    protected $casts      = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
        'dropOffDelay'          => 'int',
        'deliveryDaysWindow'    => 'int',
    ];

    /**
     * @param  \DateTimeImmutable|null $date
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DropOffDay|null
     */
    public function getForDate(DateTimeImmutable $date = null): ?DropOffDay
    {
        $dateTime = $date ?? new DateTimeImmutable('today');

        return $this->dropOffDays->first(function (DropOffDay $dropOffDay) use ($dateTime) {
            $dateMatches    = $dropOffDay->date === $dateTime;
            $weekdayMatches = $dropOffDay->weekday === (int) $dateTime->format('N');

            return $dropOffDay->dispatch && ($dateMatches || $weekdayMatches);
        });
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

        if ($this->dropOffDays->isNotEmpty()) {
            do {
                $dropOffDate = $fromDate->modify("+$day day");
                $weekday     = (int) $dropOffDate->format('w');

                /** @var DropOffDay $matchingDay */
                $matchingDay = $this->dropOffDays->firstWhere('weekday', $weekday);
                $deviation   = $deviatedDays->firstWhere('date', '==', $dropOffDate);

                $matchingDayArray = Utils::mergeArraysIgnoringNull(
                    $matchingDay ? $matchingDay->toArray() : [],
                    $deviation ? $deviation->toArray() : []
                );

                if ($matchingDayArray['dispatch']) {
                    $newDropOffDays[] = ['date' => $dropOffDate, 'weekday' => $weekday] + $matchingDayArray;
                    $items++;
                }

                $day++;
            } while ($items < $this->deliveryDaysWindow);
        }

        return new DropOffDayCollection($newDropOffDays);
    }

    /**
     * @param  null|\DateTimeImmutable $date
     *
     * @return \DateTimeImmutable|false
     */
    private function createFromDate(?DateTimeImmutable $date)
    {
        $fromDate = (new DateTimeImmutable('today'))->modify("+$this->dropOffDelay day");

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
