<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Config;

class CarrierOptionsRepository extends Repository
{
    private const ORDERED_CARRIER_GETTER = [
        'subscriptionId' => Carrier::TYPE_CUSTOM,
        'id'             => Carrier::TYPE_MAIN,
        'name'           => Carrier::TYPE_MAIN,
    ];

    /**
     * @param  int|string|null $identifier
     *
     * @return null|array
     */
    public function get($identifier): ?array
    {
        $carrierConfig = Config::get('carriers');

        return $this->retrieve("carrier_options_$identifier", function () use ($identifier, $carrierConfig) {
            foreach (self::ORDERED_CARRIER_GETTER as $key => $type) {
                $createdCarrier = Arr::first($carrierConfig, static function ($carrier) use ($key, $identifier, $type) {
                    return $identifier === ($carrier[$key] ?? null) && $type === ($carrier['type'] ?? null);
                });

                if ($createdCarrier) {
                    return $createdCarrier;
                }
            }

            return null;
        });
    }
}
