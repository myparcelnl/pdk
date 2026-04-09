<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

/**
 * This repository defines a basic caching wrapper around the CoreApi CapabilitiesService
 *
 * @package MyParcelNL\Pdk\Carrier\Repository
 */
class CarrierCapabilitiesRepository extends Repository
{
    protected CapabilitiesService $apiService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     * @param  \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService  $apiService
     */
    public function __construct(StorageInterface $storage, CapabilitiesService $apiService)
    {
        parent::__construct($storage);

        $this->apiService = $apiService;
    }

    /**
     * Return contract definitions from the API as a CarrierCollection of Carrier models.
     *
     * If a carrier name is provided, only return the contract definition for that carrier.
     *
     * @param null|string $carrier Carrier name in v2 format (eg. "POSTNL")
     * @return CarrierCollection
     */
    public function getContractDefinitions(?string $carrier = null): CarrierCollection
    {
        $cacheKey = "contractDefinitions.$carrier";

        return $this->retrieve($cacheKey, function () use ($carrier) {
            $contractDefinitions = $this->apiService->getContractDefinitions($carrier);
            // Convert contract definitions to an array so they can be cast into Carrier models
            $contractDefinitions = array_map(function ($contractDefinition) {
                // Converts all model classes to stdClass
                $serialized = $contractDefinition->jsonSerialize();
                // Now deeply convert all stdClasses to arrays so they can be cast into Carrier models
                return json_decode(json_encode($serialized), true);
            }, $contractDefinitions);

            // Convert the array of contract definitions to a CarrierCollection of Carrier models
            return new CarrierCollection($contractDefinitions);
        });
    }

    /**
     * @TODO: This is just a placeholder till we implement capabilities. Here to define architecture.
     * @param array $args
     * @return \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[]
     */
    public function getCapabilities(array $args): array
    {
        $cacheKey = 'capabilities.' . md5(json_encode($args));

        return $this->retrieve($cacheKey, function () use ($args) {
            return $this->apiService->getCapabilities($args);
        });
    }
}
