<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use MyParcelNL\Pdk\SdkApi\Service\CoreApi\AbstractCoreApiService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi;

/**
 * Abstract base class for services using the ShipmentApi.
 *
 * This intermediate layer provides a shared ShipmentApi instance for all services
 * that interact with shipment-related endpoints in the CoreAPI. The ShipmentApi class
 * contains multiple endpoint groups including:
 * - Capabilities (contract definitions, delivery options)
 * - Shipments (create, retrieve, update, delete)
 * - Labels (generate, retrieve)
 * - Track & trace
 *
 * By centralizing the ShipmentApi instantiation here, we avoid duplication across
 * multiple service classes that use the same API class.
 *
 * **Usage:**
 * Extend this class for services that need to interact with ShipmentApi endpoints.
 * Access the API instance via the protected `$shipmentApi` property.
 *
 * **Example:**
 * ```php
 * class ContractDefinitionsService extends AbstractShipmentApiService
 * {
 *     public function getContractDefinitions(string $carrier): array
 *     {
 *         return $this->executeOperationWithErrorHandling(
 *             fn() => $this->shipmentApi->postCapabilitiesContractDefinitions(...),
 *             'postContractDefinitions'
 *         );
 *     }
 * }
 * ```
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\CoreApi\AbstractCoreApiService
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi
 */
abstract class AbstractShipmentApiService extends AbstractCoreApiService
{
    /**
     * The ShipmentApi instance for making API calls.
     *
     * @var ShipmentApi
     */
    protected $shipmentApi;

    /**
     * Initialize the service with a configured ShipmentApi instance.
     */
    public function __construct()
    {
        $this->shipmentApi = new ShipmentApi(null, $this->getApiConfig());
    }
}
