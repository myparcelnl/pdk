<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ObjectSerializer;

/**
 * Test double for CarrierCapabilitiesRepository that returns permissive defaults
 * without making API calls.
 *
 * Integration tests get a "carrier supports everything" response so the calculator
 * chain can run without enqueuing mock HTTP responses.
 *
 * Tests that need specific capabilities behavior should use UsesSdkApiMock +
 * MockSdkApiHandler::enqueue() to get passthrough to the real HTTP-backed repository.
 */
class MockCarrierCapabilitiesRepository extends CarrierCapabilitiesRepository
{
    /**
     * @var bool When true, delegate to the real (HTTP-backed) implementation.
     */
    private $passthrough = false;

    public function __construct(StorageInterface $storage, CapabilitiesService $apiService)
    {
        parent::__construct($storage, $apiService);
    }

    /**
     * Enable passthrough mode so tests that enqueue specific mock responses
     * can reach the real (MockSdkApiHandler-backed) implementation.
     */
    public function enablePassthrough(): void
    {
        $this->passthrough = true;
    }

    /**
     * Disable passthrough mode (default state).
     */
    public function disablePassthrough(): void
    {
        $this->passthrough = false;
    }

    /**
     * Return permissive capabilities for the requested carrier (or all known carriers).
     *
     * @param  array $args
     *
     * @return \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[]
     */
    public function getCapabilities(array $args): array
    {
        if ($this->passthrough) {
            return parent::getCapabilities($args);
        }

        $carrierName = $args['carrier'] ?? null;

        if ($carrierName) {
            return $this->buildPermissiveCapabilities($carrierName);
        }

        // No carrier filter — return capabilities for all known carriers.
        $results = [];

        foreach (RefCapabilitiesSharedCarrierV2::getAllowableEnumValues() as $name) {
            $results = array_merge($results, $this->buildPermissiveCapabilities($name));
        }

        return $results;
    }

    /**
     * Build a single permissive capabilities result for a carrier.
     *
     * All package types, delivery types and options are available. No option is required.
     * This allows integration tests to focus on business logic without worrying about
     * capabilities gating.
     *
     * @return \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[]
     */
    private function buildPermissiveCapabilities(string $carrierName): array
    {
        $option = [
            'isSelectedByDefault' => false,
            'isRequired'          => false,
            'requires'            => [],
            'excludes'            => [],
        ];

        $result = [
            'carrier'            => $carrierName,
            'contract'           => ['id' => 100, 'type' => 'MAIN'],
            'packageTypes'       => ['PACKAGE', 'MAILBOX', 'DIGITAL_STAMP', 'SMALL_PACKAGE', 'UNFRANKED'],
            'options'            => [
                'requiresSignature'           => $option,
                'recipientOnlyDelivery'       => $option,
                'requiresAgeVerification'     => $option,
                'oversizedPackage'            => $option,
                'hideSender'                  => $option,
                'returnOnFirstFailedDelivery' => $option,
                'sameDayDelivery'             => $option,
                'saturdayDelivery'            => $option,
                'tracked'                     => $option,
                'insurance'                   => $option,
                'priorityDelivery'            => $option,
                'requiresReceiptCode'         => $option,
                'scheduledCollection'         => $option,
                'cooledDelivery'              => $option,
                'freshFood'                   => $option,
                'frozen'                      => $option,
                'deliverAtPostalPoint'        => $option,
            ],
            'physicalProperties' => [
                'weight' => [
                    'min' => ['value' => 1, 'unit' => 'g'],
                    'max' => ['value' => 30000, 'unit' => 'g'],
                ],
            ],
            'deliveryTypes'      => ['STANDARD_DELIVERY'],
            'transactionTypes'   => [],
            'collo'              => ['max' => 1],
        ];

        $json = json_decode(json_encode(['results' => [$result]]));

        return ObjectSerializer::deserialize(
            $json->results,
            '\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[]'
        );
    }
}
