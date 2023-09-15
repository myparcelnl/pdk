<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Service;

use DateTimeImmutable;
use DateTimeInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

class DropOffService implements DropOffServiceInterface
{
    private const DELIVERY_DAYS_WINDOW_MAXIMUM = 14;

    /**
     * @param  \DateTimeImmutable|null $date
     */
    public function getForDate(CarrierSettings $settings, DateTimeImmutable $date = null): ?DropOffDay
    {
        $dateTime = $date ?? new DateTimeImmutable('today');

        return $settings->dropOffPossibilities->dropOffDays->first(function (DropOffDay $dropOffDay) use ($dateTime) {
            $dateMatches    = $dropOffDay->date === $dateTime;
            $weekdayMatches = $dropOffDay->weekday === (int) $dateTime->format('N');

            return $dropOffDay->dispatch && ($dateMatches || $weekdayMatches);
        });
    }

    /**
     * @param  \DateTimeImmutable|null $date
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getPossibleDropOffDays(
        CarrierSettings   $settings,
        DateTimeImmutable $date = null
    ): DropOffDayCollection {
        $fromDate     = $this->createFromDate($settings, $date);
        $deviatedDays = $this->getRelevantDeviatedDropOffDays($settings, $fromDate);

        $newDropOffDays = [];
        $day            = 0;
        $items          = 0;

        if ($settings->dropOffPossibilities->dropOffDays->isNotEmpty()) {
            do {
                if (self::DELIVERY_DAYS_WINDOW_MAXIMUM < $day) {
                    break;
                }

                $dropOffDate = $fromDate->modify("+$day day");
                $weekday     = (int) $dropOffDate->format('w');

                /** @var DropOffDay $matchingDay */
                $matchingDay = $settings->dropOffPossibilities->dropOffDays->firstWhere('weekday', $weekday);
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
            } while ($items < $settings->deliveryDaysWindow);
        }

        return new DropOffDayCollection($newDropOffDays);
    }

    /**
     * @param  null|\DateTimeImmutable $date
     *
     * @return \DateTimeImmutable|false
     */
    private function createFromDate(CarrierSettings $settings, ?DateTimeImmutable $date)
    {
        $fromDate = (new DateTimeImmutable('today'))->modify("+$settings->dropOffDelay day");

        if ($date) {
            $newDate  = $date->setTime(0, 0);
            $fromDate = $newDate ?: $fromDate;
        }

        return $fromDate;
    }

    private function getRelevantDeviatedDropOffDays(
        CarrierSettings   $settings,
        DateTimeInterface $minDate
    ): DropOffDayCollection {
        return $settings->dropOffPossibilities->dropOffDaysDeviations
            ->where('date', '>=', $minDate)
            ->where('date', '<=', $minDate->modify('+1 year'));
    }
}
