<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use DateTime;
use Exception;
use MyParcelNL\Pdk\Base\Settings;
use MyParcelNL\Pdk\Carrier\Model\DropOffOptions;
use MyParcelNL\Pdk\Shipment\Collection\DeliveryDayCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryDay;

class DeliveryDateService
{
    public const DATE_FORMAT         = 'Y-m-d H:i:s';
    public const DEFAULT_CUTOFF_TIME = '17:00';

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

    public static function getDeliveryDays(DateTime $today): DeliveryDayCollection
    {
        $dropOffOptions = new DropOffOptions(Settings::get('settings'));
        /** @var DeliveryDayCollection $dropOffDays */
        $dropOffDays        = $dropOffOptions->getDropOffDays();
        $dropOffDelay       = $dropOffOptions->getDropOffDelay();
        $deliveryDaysWindow = $dropOffOptions->getDeliveryDaysWindow();
        $today              = $today->setTime(0, 0);
        $minDate            = clone $today->modify("+$dropOffDelay day");
        $maxDate            = clone $today->modify("+$deliveryDaysWindow day");
        $exceptionsCalendar = $dropOffOptions->getDropOffDaysException();

        $exceptionDays = $exceptionsCalendar
            ->where('date', '<=', $maxDate)
            ->where('date', '>=', $minDate);

        $updatedDropOffDays = $dropOffDays->map(function (DeliveryDay $dropOffDay) use ($exceptionDays) {
           $exceptionDate = $exceptionDays->where('date', '==', $dropOffDay->date);

           if ($exceptionDate->isEmpty()) {
               return $dropOffDay;
           }

           return $exceptionDate;
        });

        return $updatedDropOffDays->where('dispatch', '!=', false)->slice($dropOffDelay, $deliveryDaysWindow);
    }
}


