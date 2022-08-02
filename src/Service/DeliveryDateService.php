<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use DateTime;
use Exception;
use MyParcelNL\Pdk\Base\Settings;
use MyParcelNL\Pdk\Carrier\Model\DropOffDayPossibilities;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

class DeliveryDateService
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param  string|DateTime $deliveryDate
     *
     * @return string
     * @throws \Exception
     */
    public static function fixPastDeliveryDate($deliveryDate): string
    {
        $tomorrow = new DateTime('tomorrow');

        try {
            $deliveryDateObject = is_a($deliveryDate, DateTime::class) ? $deliveryDate : new DateTime($deliveryDate);
        } catch (Exception $e) {
            return $tomorrow->format(self::DATE_FORMAT);
        }

        $oldDate = clone $deliveryDateObject;
        $tomorrow->setTime(0, 0);
        $oldDate->setTime(0, 0);

        if ($deliveryDateObject < $tomorrow || '0' === $deliveryDateObject->format('w')) {
            $deliveryDateObject = $tomorrow;
        }

        return $deliveryDateObject->format(self::DATE_FORMAT);
    }

    public static function getDeliveryDays(DateTime $today): DropOffDayCollection
    {
        $dropOffDayPossibilities = new DropOffDayPossibilities(Settings::get('settings'));
        /** @var \MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection $dropOffDays */
        $dropOffDays        = $dropOffDayPossibilities->getDropOffDays();
        $dropOffDelay       = $dropOffDayPossibilities->getDropOffDelay();
        $deliveryDaysWindow = $dropOffDayPossibilities->getDeliveryDaysWindow();

        $exceptionDays = self::getExceptionDays($dropOffDayPossibilities, $today);

        $updatedDropOffDays = $dropOffDays->map(function (DropOffDay $dropOffDay) use ($exceptionDays) {
            $exceptionDateCollection = $exceptionDays->where('date', '==', $dropOffDay->date);

            if ($exceptionDateCollection->isEmpty()) {
                return $dropOffDay;
            }

            return self::mergeDropOffDayPossibilities($dropOffDay, $exceptionDateCollection->first());
        });

        return $updatedDropOffDays->where('dispatch', '!=', false)
            ->slice($dropOffDelay, $deliveryDaysWindow);
    }

    private static function mergeDropOffDayPossibilities(DropOffDay $dropOffDay, DropOffDay $exceptionDay): DropOffDay
    {
        $dropOffDayArray              = $dropOffDay->toArray();
        $exceptionDayArray            = $exceptionDay->toArray();
        $exceptionDayArrayWithoutNull = array_filter($exceptionDayArray, static function ($value) {
            return null !== $value;
        });
        $mergedDropOffDays            = array_replace($dropOffDayArray, $exceptionDayArrayWithoutNull);

        return new DropOffDay($mergedDropOffDays);
    }

    private static function getExceptionDays(
        DropOffDayPossibilities $dropOffDayPossibilities,
        DateTime                $today
    ): DropOffDayCollection {
        $dropOffDelay       = $dropOffDayPossibilities->getDropOffDelay();
        $deliveryDaysWindow = $dropOffDayPossibilities->getDeliveryDaysWindow();
        $today              = $today->setTime(0, 0);
        $minDate            = clone $today->modify("+$dropOffDelay day");
        $maxDate            = clone $today->modify("+$deliveryDaysWindow day");
        $exceptionsCalendar = $dropOffDayPossibilities->getDropOffDaysException();

        return $exceptionsCalendar
            ->where('date', '<=', $maxDate)
            ->where('date', '>=', $minDate);
    }
}


