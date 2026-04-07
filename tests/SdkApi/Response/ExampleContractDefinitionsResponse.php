<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi\Response;

/**
 * Mock response for CapabilitiesService::getContractDefinitions().
 *
 * Body shape: CapabilitiesResponsesContractDefinitionsV2 → {"items": [...]}
 * Each item is a RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2.
 *
 * Field names use the JSON attribute names from the openapi spec
 * (RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2::$attributeMap):
 *   carrier           → 'carrier'
 *   package_types     → 'packageTypes'
 *   delivery_types    → 'deliveryTypes'
 *   options           → 'options'
 *   transaction_types → 'transactionTypes'
 *   collo             → 'collo'
 *
 * Carrier names are values from CapabilitiesPostContractDefinitionsRequestV2::getCarrierAllowableValues().
 * Package type strings are values from RefShipmentPackageTypeV2::getAllowableEnumValues().
 * Delivery type strings are values from RefTypesDeliveryTypeV2::getAllowableEnumValues().
 *
 * Pass a custom $items array to the constructor to override defaults for a specific test:
 *   new ExampleContractDefinitionsResponse([['carrier' => 'POSTNL', ...]]);
 *
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesContractDefinitionsV2
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2
 */
class ExampleContractDefinitionsResponse extends SdkJsonResponse
{
    /**
     * @return array
     */
    protected function getContent(): array
    {
        return [
            'items' => $this->responseContent ?? $this->getDefaultItems(),
        ];
    }

    /**
     * Minimal realistic contract definitions for the carriers available via
     * CapabilitiesPostContractDefinitionsRequestV2::getCarrierAllowableValues().
     *
     * Note: These are just theoretical examples for testing purposes, not necessarily the actual real-world values.
     *
     * @return array[]
     */
    protected function getDefaultItems(): array
    {
        return [
            [
                'carrier'          => 'POSTNL',
                'packageTypes'     => ['PACKAGE', 'MAILBOX', 'UNFRANKED', 'DIGITAL_STAMP', 'SMALL_PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY', 'MORNING_DELIVERY', 'EVENING_DELIVERY', 'PICKUP_DELIVERY'],
                'transactionTypes' => ['B2C', 'B2B'],
                'options'          => [
                    'requiresAgeVerification'      => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'insurance'                    => [
                        'isSelectedByDefault' => false,
                        'isRequired'          => false,
                        'insuredAmount'       => [
                            'default' => ['amount' => 0,      'currency' => 'EUR'],
                            'max'     => ['amount' => 500000, 'currency' => 'EUR'],
                            'min'     => ['amount' => 0,      'currency' => 'EUR'],
                        ],
                    ],
                    'oversizedPackage'             => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'recipientOnlyDelivery'        => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'printReturnLabelAtDropOff'    => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'priorityDelivery'             => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'requiresReceiptCode'          => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'returnOnFirstFailedDelivery'  => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'requiresSignature'            => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'noTracking'                   => ['isSelectedByDefault' => false, 'isRequired' => false],
                    'tracked'                      => ['isSelectedByDefault' => false, 'isRequired' => false], // Tracked is deprecated but may still be included in the response
                ],
                'collo'            => ['max' => 10],
            ],
            [
                'carrier'          => 'DHL_FOR_YOU',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY', 'PICKUP_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
            [
                'carrier'          => 'DHL_PARCEL_CONNECT',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
            [
                'carrier'          => 'DHL_EUROPLUS',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
            [
                'carrier'          => 'DPD',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
            [
                'carrier'          => 'BRT',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
            [
                'carrier'          => 'INPOST',
                'packageTypes'     => ['PACKAGE'],
                'deliveryTypes'    => ['STANDARD_DELIVERY', 'PICKUP_DELIVERY'],
                'options'          => null,
                'transactionTypes' => null,
                'collo'            => null,
            ],
        ];
    }
}
