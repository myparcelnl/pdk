<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

beforeEach(function () {
    TestBootstrapper::forPlatform(Proposition::MYPARCEL_NAME);
});

it('maps carrier name to legacy identifier', function (string $carrierName, string $expectedLegacyName) {
    /** @var FrontendDataAdapterInterface $service */
    $service = Pdk::get(FrontendDataAdapterInterface::class);

    expect($service->getLegacyCarrierIdentifier($carrierName))->toBe($expectedLegacyName);
})->with(function () {
    foreach (ApiMapperService::forCarrier()->allRows() as $constantName => $row) {
        $v2Name     = $row[ApiMapperService::COLUMN_V2_NAME];
        $legacyName = $row[ApiMapperService::COLUMN_LEGACY_NAME];

        if (null !== $v2Name && null !== $legacyName) {
            yield $constantName => [$v2Name, $legacyName];
        }
    }
});

it('removes separators from a carrier without a legacy name', function () {
    /** @var FrontendDataAdapterInterface $service */
    $service = Pdk::get(FrontendDataAdapterInterface::class);

    expect($service->getLegacyCarrierIdentifier('NEW_CARRIER'))->toBe('newcarrier');
});
