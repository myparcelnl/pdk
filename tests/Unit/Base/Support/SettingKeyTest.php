<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use function expect;

it('builds an allow-key with capitalised suffix', function () {
    expect(SettingKey::allow('signature'))->toBe('allowSignature');
});

it('builds a price-key with capitalised suffix', function () {
    expect(SettingKey::price('signature'))->toBe('priceSignature');
});

it('builds an export-key with capitalised suffix', function () {
    expect(SettingKey::export('signature'))->toBe('exportSignature');
});

it('builds a priceDeliveryType-key with capitalised suffix', function () {
    expect(SettingKey::priceDeliveryType('evening'))->toBe('priceDeliveryTypeEvening');
});

it('strips a trailing "Delivery" from priceDeliveryType keys for legacy compatibility', function () {
    expect(SettingKey::priceDeliveryType('eveningDelivery'))->toBe('priceDeliveryTypeEvening')
        ->and(SettingKey::priceDeliveryType('sameDayDelivery'))->toBe('priceDeliveryTypeSameDay');
});

it('maps pickupDelivery allow-key to the legacy "allowPickupLocations" attribute', function () {
    expect(SettingKey::allow('pickupDelivery'))->toBe('allowPickupLocations');
});

it('builds a pricePackageType-key with capitalised suffix', function () {
    expect(SettingKey::pricePackageType('mailbox'))->toBe('pricePackageTypeMailbox')
        ->and(SettingKey::pricePackageType('packageSmall'))->toBe('pricePackageTypePackageSmall')
        ->and(SettingKey::pricePackageType('digitalStamp'))->toBe('pricePackageTypeDigitalStamp');
});
