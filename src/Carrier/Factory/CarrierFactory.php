<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Factory;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;

class CarrierFactory
{
    public const  KEY_CARRIER_ID         = 'id';
    public const  KEY_CARRIER_NAME       = 'name';
    public const  KEY_CONTRACT_ID        = 'contractId';
    public const  TYPE_NAME              = 'type';
    public const  TYPE_VALUE_CUSTOM      = 'custom';
    public const  TYPE_VALUE_MAIN        = 'main';
    private const ORDERED_CARRIER_GETTER = [
        self::KEY_CARRIER_ID   => self::TYPE_VALUE_MAIN,
        self::KEY_CONTRACT_ID  => self::TYPE_VALUE_CUSTOM,
        self::KEY_CARRIER_NAME => self::TYPE_VALUE_MAIN,
    ];

    private static $config = [];

    /**
     * @param  int|string $carrier
     * @param  array|null $alternateConfig
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
            $createdCarrier = self::createFrom($key, $carrier, $typeValue);

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
     * @param  string $key
     * @param         $value
     * @param  string $type
     *
     * @return array
     */
    public static function createFrom(string $key, $value, string $type): array
    {
        $carrier = array_filter(self::$config['carriers'], static function ($row) use ($key, $value, $type) {
            return ($value === $row[$key] && $type === $row[self::TYPE_NAME]);
        }, ARRAY_FILTER_USE_BOTH);

        return $carrier[0] ?? [];
    }
}
