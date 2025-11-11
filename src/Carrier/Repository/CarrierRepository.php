<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class CarrierRepository extends Repository implements CarrierRepositoryInterface
{
    private const ORDERED_CARRIER_GETTER = ['id', 'name'];

    protected PropositionService $propositionService;

    public function __construct(StorageInterface $storage, PropositionService $propositionService)
    {
        parent::__construct($storage);
        $this->propositionService = $propositionService;
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function all(): CarrierCollection
    {
        try {
            return $this->propositionService->getCarriers();
        } catch (InvalidArgumentException $e) {
            // Silently fail, errors are already logged elsewhere - we just don't know the carriers here
            return new CarrierCollection();
        }
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
                // Support legacy name lookups
                if ($key === 'name') {
                    $legacyMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
                    $input[$key] = $legacyMap[$input[$key]] ?? $input[$key];
                }

                $carrier = $collection->firstWhere($key, $input[$key]);

                if ($carrier) {
                    break;
                }
            }

            // Migrate deprecated UPS carrier name "ups" to UPS Standard
            if (!$carrier && isset($input['name']) && $input['name'] === Carrier::CARRIER_UPS_NAME) {
                $carrier = $collection->firstWhere('id', Carrier::CARRIER_UPS_STANDARD_ID);
            }

            if (!$carrier) {
                Logger::warning(sprintf('Carrier %s not found', json_encode($input)));
            }

            return $carrier;
        });
    }
}
