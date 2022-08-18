<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Service;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class DeliveryDateService
{
    /**
     * @param  string|\DateTimeInterface $deliveryDate
     *
     * @return \DateTimeInterface
     */
    public static function fixPastDeliveryDate($deliveryDate): DateTimeInterface
    {
        $tomorrow        = new DateTimeImmutable('tomorrow');
        $newDeliveryDate = $tomorrow;

        try {
            $deliveryDateObject = is_a($deliveryDate, DateTimeInterface::class)
                ? $deliveryDate
                : new DateTimeImmutable($deliveryDate);

            // todo: instead of checking for Sunday, incorporate the plugin settings.
            $notOnSunday = '0' !== $deliveryDateObject->format('w');

            if ($deliveryDateObject >= $tomorrow && $notOnSunday) {
                $newDeliveryDate = $deliveryDateObject;
            }
        } catch (Exception $e) {
            // Occurs when nonsense is fed to new DateTimeImmutable().
        }

        return $newDeliveryDate;
    }
}


