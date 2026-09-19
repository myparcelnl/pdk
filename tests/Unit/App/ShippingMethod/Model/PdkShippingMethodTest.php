<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('resolves a fixed package type when the shipping method id is mapped to one', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection([
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => ['flat_rate:32'],
        ]))
        ->store();

    $method = new PdkShippingMethod(['id' => 'flat_rate:32']);

    expect($method->allowedPackageTypes->pluck('name')->toArray())
        ->toBe([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
});

it('falls back to all carrier package types when the shipping method id is mapped to INHERIT', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection([
            (string) TriStateService::INHERIT => ['flat_rate:32'],
        ]))
        ->store();

    $method = new PdkShippingMethod(['id' => 'flat_rate:32']);

    expect($method->allowedPackageTypes->isNotEmpty())->toBeTrue();
});

it('returns no allowed package types when the shipping method id is not assigned to any key', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection([
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => ['some_other_method'],
        ]))
        ->store();

    $method = new PdkShippingMethod(['id' => 'flat_rate:32']);

    expect($method->allowedPackageTypes->isEmpty())->toBeTrue();
});

it('returns each matching package type when a shipping method is assigned to multiple keys', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection([
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME => ['flat_rate:32'],
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => ['flat_rate:32'],
            DeliveryOptions::PACKAGE_TYPE_LETTER_NAME  => ['some_other_method'],
        ]))
        ->store();

    $method = new PdkShippingMethod(['id' => 'flat_rate:32']);

    expect($method->allowedPackageTypes->pluck('name')->toArray())
        ->toBe([
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        ]);
});

it('falls back to all carrier types when INHERIT is among the matched keys, even alongside a specific type', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection([
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME => ['flat_rate:32'],
            (string) TriStateService::INHERIT          => ['flat_rate:32'],
        ]))
        ->store();

    $method = new PdkShippingMethod(['id' => 'flat_rate:32']);

    expect($method->allowedPackageTypes->count())->toBeGreaterThan(1);
});

it('includes all mapped SDK carrier types once and skips unknown types', function () {
    $rows = array_filter(ApiMapperService::forPackageType()->allRows(), static function (array $row): bool {
        return ! in_array(null, $row, true);
    });
    $v2Names = array_column($rows, 'v2_name');

    factory(Carrier::class)
        ->fromPostNL()
        ->withPackageTypes(array_merge($v2Names, $v2Names, ['UNKNOWN_PACKAGE']))
        ->store();

    $actual   = (new PdkShippingMethod(['id' => 'flat_rate:32']))->allowedPackageTypes;
    $expected = array_column($rows, 'id', 'legacy_name');

    expect($actual->pluck('id', 'name')->toArray())->toEqual($expected)
        ->and($actual->count())->toBe(count($rows));
});

it('ignores an unknown package type in shipping method settings', function () {
    factory(CheckoutSettings::class)
        ->withAllowedShippingMethods(new Collection(['unknown_package' => ['flat_rate:32']]))
        ->store();

    expect((new PdkShippingMethod(['id' => 'flat_rate:32']))->allowedPackageTypes->isEmpty())->toBeTrue();
});
