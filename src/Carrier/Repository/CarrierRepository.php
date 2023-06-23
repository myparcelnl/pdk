<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Config;

class CarrierRepository extends Repository implements CarrierRepositoryInterface
{
    private const ORDERED_CARRIER_GETTER = [
        'subscriptionId' => Carrier::TYPE_CUSTOM,
        'id'             => Carrier::TYPE_MAIN,
        'name'           => Carrier::TYPE_MAIN,
    ];

    /**
     * @return array[]|Collection
     */
    public function all(): Collection
    {
        return $this->retrieve('carrier_collection', function () {
            return new Collection(Config::get('carriers'));
        });
    }

    /**
     * @param  array $input
     *
     * @return null|array
     */
    public function get(array $input): ?array
    {
        $hash       = md5(json_encode($input));
        $collection = $this->all();

        return $this->retrieve("carrier_options_$hash", function () use ($input, $collection) {
            $carrier = null;

            foreach (self::ORDERED_CARRIER_GETTER as $key => $type) {
                if (! isset($input[$key])) {
                    continue;
                }

                $carrier = $collection
                    ->where('type', $type)
                    ->firstWhere($key, $input[$key]);

                if ($carrier) {
                    break;
                }
            }

            return $carrier;
        });
    }
}
