<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Resource;

use MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

describe('DeliveryOptionsV1Resource', function () {
    describe('format()', function () {
        it('formats delivery options with shipment options as CONSTANT_CASE array', function () {
            $shipmentOptions = new ShipmentOptions([
                'signature' => TriStateService::ENABLED,
                'onlyRecipient' => TriStateService::ENABLED,
                'return' => TriStateService::DISABLED,
                'largeFormat' => TriStateService::ENABLED,
                'ageCheck' => TriStateService::ENABLED,
                'insurance' => 50000,
            ]);

            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => $shipmentOptions,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => '12345',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result)
                ->toHaveKey('orderId', '12345')
                ->toHaveKey('deliveryOptions')
                ->and($result['deliveryOptions'])
                ->toHaveKey('shipmentOptions')
                ->and($result['deliveryOptions']['shipmentOptions'])
                ->toBeArray()
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME)
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME)
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME)
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME)
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME)
                ->not()->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME);
        });

        it('returns empty array when no shipment options are enabled', function () {
            $shipmentOptions = new ShipmentOptions([
                'signature' => TriStateService::DISABLED,
                'onlyRecipient' => TriStateService::DISABLED,
                'return' => TriStateService::DISABLED,
                'largeFormat' => TriStateService::DISABLED,
                'ageCheck' => TriStateService::DISABLED,
                'insurance' => 0,
            ]);

            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => $shipmentOptions,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => '67890',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result['deliveryOptions']['shipmentOptions'])
                ->toBeArray()
                ->toBeEmpty();
        });

        it('returns empty array when shipment options is null', function () {
            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => null,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => 'test',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result['deliveryOptions']['shipmentOptions'])
                ->toBeArray()
                ->toBeEmpty();
        });

        it('includes INSURANCE when insurance amount is greater than 0', function () {
            $shipmentOptions = new ShipmentOptions([
                'insurance' => 25000,
            ]);

            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => $shipmentOptions,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => 'test',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result['deliveryOptions']['shipmentOptions'])
                ->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME);
        });

        it('excludes INSURANCE when insurance amount is 0', function () {
            $shipmentOptions = new ShipmentOptions([
                'insurance' => 0,
            ]);

            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => $shipmentOptions,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => 'test',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result['deliveryOptions']['shipmentOptions'])
                ->not()->toContain(PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME);
        });

        it('uses correct CONSTANT_CASE names from PropositionCarrierFeatures', function () {
            $shipmentOptions = new ShipmentOptions([
                'signature' => TriStateService::ENABLED,
                'onlyRecipient' => TriStateService::ENABLED,
                'return' => TriStateService::ENABLED,
                'largeFormat' => TriStateService::ENABLED,
                'ageCheck' => TriStateService::ENABLED,
                'insurance' => 10000,
            ]);

            $deliveryOptions = new DeliveryOptions([
                'shipmentOptions' => $shipmentOptions,
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => 'test',
                'deliveryOptions' => $deliveryOptions,
            ]);

            $expectedOptions = [
                PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME,
            ];

            expect($result['deliveryOptions']['shipmentOptions'])
                ->toBe($expectedOptions);
        });

        it('formats carrier, packageType, and deliveryType to CONSTANT_CASE', function () {
            $carrier = new Carrier(['name' => 'postnl']);

            $deliveryOptions = new DeliveryOptions([
                'carrier' => $carrier,
                'packageType' => 'package',
                'deliveryType' => 'standard',
            ]);

            $result = DeliveryOptionsV1Resource::format([
                'orderId' => 'test123',
                'deliveryOptions' => $deliveryOptions,
            ]);

            expect($result['deliveryOptions'])
                ->toHaveKey('carrier', Carrier::CARRIER_POSTNL_NAME)
                ->toHaveKey('packageType', PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_NAME)
                ->toHaveKey('deliveryType', PropositionCarrierFeatures::DELIVERY_TYPE_STANDARD_NAME);
        });

        it('handles legacy carrier names correctly', function () {
            $testCases = [
                ['dhlforyou', Carrier::CARRIER_DHL_FOR_YOU_NAME],
                ['postnl', Carrier::CARRIER_POSTNL_NAME],
                ['dpd', Carrier::CARRIER_DPD_NAME],
                ['ALREADY_CONSTANT', 'ALREADY_CONSTANT'], // Already constant case
            ];

            foreach ($testCases as [$input, $expected]) {
                $carrier = new Carrier(['name' => $input]);
                $deliveryOptions = new DeliveryOptions(['carrier' => $carrier]);

                $result = DeliveryOptionsV1Resource::format([
                    'orderId' => 'test',
                    'deliveryOptions' => $deliveryOptions,
                ]);

                expect($result['deliveryOptions']['carrier'])->toBe($expected);
            }
        });

        it('handles all package types correctly', function () {
            $testCases = [
                ['package', PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_NAME],
                ['mailbox', PropositionCarrierFeatures::PACKAGE_TYPE_MAILBOX_NAME],
                ['letter', PropositionCarrierFeatures::PACKAGE_TYPE_LETTER_NAME],
                ['digital_stamp', PropositionCarrierFeatures::PACKAGE_TYPE_DIGITAL_STAMP_NAME],
                ['package_small', PropositionCarrierFeatures::PACKAGE_TYPE_PACKAGE_SMALL_NAME],
            ];

            foreach ($testCases as [$input, $expected]) {
                $deliveryOptions = new DeliveryOptions(['packageType' => $input]);

                $result = DeliveryOptionsV1Resource::format([
                    'orderId' => 'test',
                    'deliveryOptions' => $deliveryOptions,
                ]);

                expect($result['deliveryOptions']['packageType'])->toBe($expected);
            }
        });

        it('handles all delivery types correctly', function () {
            $testCases = [
                ['standard', PropositionCarrierFeatures::DELIVERY_TYPE_STANDARD_NAME],
                ['morning', PropositionCarrierFeatures::DELIVERY_TYPE_MORNING_NAME],
                ['evening', PropositionCarrierFeatures::DELIVERY_TYPE_EVENING_NAME],
                ['pickup', PropositionCarrierFeatures::DELIVERY_TYPE_PICKUP_NAME],
                ['same_day', PropositionCarrierFeatures::DELIVERY_TYPE_SAME_DAY_NAME],
            ];

            foreach ($testCases as [$input, $expected]) {
                $deliveryOptions = new DeliveryOptions(['deliveryType' => $input]);

                $result = DeliveryOptionsV1Resource::format([
                    'orderId' => 'test',
                    'deliveryOptions' => $deliveryOptions,
                ]);

                expect($result['deliveryOptions']['deliveryType'])->toBe($expected);
            }
        });
    });
});
