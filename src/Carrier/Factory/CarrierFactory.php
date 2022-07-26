<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Factory;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Sdk\src\Support\Arr;

class CarrierFactory
{
    private const ORDERED_CARRIER_GETTER = [
        'id'             => Carrier::TYPE_VALUE_MAIN,
        'subscriptionId' => Carrier::TYPE_VALUE_CUSTOM,
        'name'           => Carrier::TYPE_VALUE_MAIN,
    ];

    private static $config = [];

    /**
     * @param  int|string|Carrier $carrier
     * @param  array|null         $alternateConfig
     *
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     * @throws \Exception
     */
    public static function create($carrier, array $alternateConfig = null): Carrier
    {
        if (is_a($carrier, Carrier::class)) {
            return $carrier;
        }

        self::$config = $alternateConfig ?? Config::get('carriers');

        foreach (self::ORDERED_CARRIER_GETTER as $key => $typeValue) {
            $createdCarrier = self::findCarrier($key, $carrier, $typeValue);

            if ($createdCarrier) {
                return new Carrier($createdCarrier);
            }
        }

        DefaultLogger::warning('Could not find any carrier inside config', [
            'carrier' => $carrier,
            'config'  => self::$config,
        ]);

        return new Carrier([]);
    }

    /**
     * @param  string     $key
     * @param  int|string $value
     * @param  string     $type
     *
     * @return null|array
     */
    private static function findCarrier(string $key, $value, string $type): ?array
    {
        return Arr::first(self::$config['carriers'], static function ($row) use ($key, $value, $type) {
            return ($value === $row[$key] && $type === $row[Carrier::TYPE_NAME]);
        });
    }
}
