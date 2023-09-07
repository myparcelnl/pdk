<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

class CarrierRepository extends Repository implements CarrierRepositoryInterface
{
    private const ORDERED_CARRIER_GETTER = ['id', 'name'];

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function all(): CarrierCollection
    {
        return Pdk::get('allCarriers');
    }

    /**
     * @param  array $input
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function get(array $input): ?Carrier
    {
        $hash       = md5(json_encode($input));
        $collection = $this->all();

        return $this->retrieve("carrier_options_$hash", function () use ($input, $collection) {
            $carrier = null;

            foreach (self::ORDERED_CARRIER_GETTER as $key) {
                if (! isset($input[$key])) {
                    continue;
                }

                $carrier = $collection->firstWhere($key, $input[$key]);

                if ($carrier) {
                    break;
                }
            }

            return $carrier;
        });
    }
}
