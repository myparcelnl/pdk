<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

beforeEach(function () {
    TestBootstrapper::forPlatform(Proposition::MYPARCEL_NAME);
});

it('maps carrier name to legacy identifier', function (string $carrierName, string $expectedLegacyName) {
    /** @var FrontendDataAdapterInterface $service */
    $service = Pdk::get(FrontendDataAdapterInterface::class);

    expect($service->getLegacyCarrierIdentifier($carrierName))->toBe($expectedLegacyName);
})->with([
    'POSTNL'             => [RefCapabilitiesSharedCarrierV2::POSTNL, Carrier::CARRIER_POSTNL_LEGACY_NAME],
    'BPOST'              => [RefCapabilitiesSharedCarrierV2::BPOST, Carrier::CARRIER_BPOST_LEGACY_NAME],
    'CHEAP_CARGO'        => [RefCapabilitiesSharedCarrierV2::CHEAP_CARGO, Carrier::CARRIER_CHEAP_CARGO_LEGACY_NAME],
    'DPD'                => [RefCapabilitiesSharedCarrierV2::DPD, Carrier::CARRIER_DPD_LEGACY_NAME],
    'DHL_FOR_YOU'        => [RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU, Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME],
    'DHL_PARCEL_CONNECT' => [RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT, Carrier::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME],
    'DHL_EUROPLUS'       => [RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS, Carrier::CARRIER_DHL_EUROPLUS_LEGACY_NAME],
    'UPS_STANDARD'       => [RefCapabilitiesSharedCarrierV2::UPS_STANDARD, Carrier::CARRIER_UPS_STANDARD_LEGACY_NAME],
    'UPS_EXPRESS_SAVER'  => [RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER, Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME],
    'GLS'                => [RefCapabilitiesSharedCarrierV2::GLS, Carrier::CARRIER_GLS_LEGACY_NAME],
    'BRT'                => [RefCapabilitiesSharedCarrierV2::BRT, Carrier::CARRIER_BRT_LEGACY_NAME],
    'TRUNKRS'            => [RefCapabilitiesSharedCarrierV2::TRUNKRS, Carrier::CARRIER_TRUNKRS_LEGACY_NAME],
]);
