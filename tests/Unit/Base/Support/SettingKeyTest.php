<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

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

it('accepts SDK V2 SCREAMING_SNAKE_CASE consts and normalises them', function () {
    expect(SettingKey::allow(RefTypesDeliveryTypeV2::EVENING))->toBe('allowEveningDelivery')
        ->and(SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP))->toBe('allowPickupLocations')
        ->and(SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::SAME_DAY))->toBe('priceDeliveryTypeSameDay')
        ->and(SettingKey::pricePackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP))->toBe('pricePackageTypeDigitalStamp');
});

it('maps SDK V2 SMALL_PACKAGE to the legacy packageSmall attribute name', function () {
    expect(SettingKey::pricePackageType(RefShipmentPackageTypeV2::SMALL_PACKAGE))
        ->toBe('pricePackageTypePackageSmall');
});

it('handles all-caps single-word inputs (PACKAGE, MAILBOX)', function () {
    expect(SettingKey::pricePackageType(RefShipmentPackageTypeV2::MAILBOX))->toBe('pricePackageTypeMailbox')
        ->and(SettingKey::pricePackageType(RefShipmentPackageTypeV2::PACKAGE))->toBe('pricePackageTypePackage');
});
