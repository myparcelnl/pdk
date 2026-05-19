<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('checkout', 'capabilities');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('supportsShipmentOption returns true when the capability key is present in carrier options', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsShipmentOption($carrier, SignatureDefinition::class))->toBeTrue();
});

it('supportsShipmentOption returns false when the capability key is absent', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withMinimalCapabilities()
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsShipmentOption($carrier, SignatureDefinition::class))->toBeFalse();
});

it('supportsShipmentOption accepts a definition instance as well as a class string', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsShipmentOption($carrier, new SignatureDefinition()))->toBeTrue();
});

it('supportsMailbox returns true when MAILBOX is in packageTypes', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX])
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsMailbox($carrier))->toBeTrue();
});

it('supportsMailbox returns false when MAILBOX is not in packageTypes', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsMailbox($carrier))->toBeFalse();
});

it('supportsDigitalStamp returns true when DIGITAL_STAMP is in packageTypes', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withPackageTypes([RefShipmentPackageTypeV2::DIGITAL_STAMP])
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsDigitalStamp($carrier))->toBeTrue();
});

it('supportsDigitalStamp returns false when DIGITAL_STAMP is not in packageTypes', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsDigitalStamp($carrier))->toBeFalse();
});

it('supportsMultiCollo returns true when collo.max exceeds 1', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withCapabilityMultiCollo(10)
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsMultiCollo($carrier))->toBeTrue();
});

it('supportsMultiCollo returns false when collo.max is 1 (single-collo carrier)', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withCapabilityMultiCollo(1)
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->supportsMultiCollo($carrier))->toBeFalse();
});

it('getAllowedInsuranceAmounts returns an empty array when insurance is not available', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withMinimalCapabilities()
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->getAllowedInsuranceAmounts($carrier))->toEqual([]);
});

it('getAllowedInsuranceAmounts returns the tier ladder when insurance is available', function () {
    $carrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->withInsurance(0, 0, 200_000)
        ->make();

    /** @var CarrierValidationService $service */
    $service = Pdk::get(CarrierValidationService::class);

    expect($service->getAllowedInsuranceAmounts($carrier))
        ->toEqual([0, 10_000, 25_000, 50_000, 100_000, 150_000, 200_000]);
});
