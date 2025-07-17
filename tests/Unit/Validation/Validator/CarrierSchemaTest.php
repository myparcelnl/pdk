<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use BadMethodCallException;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createSchema(): CarrierSchema
{
    $carrier = factory(Carrier::class)
        ->withName('fake')
        ->withCapabilities(factory(CarrierCapabilities::class)->withEverything())
        ->make();

    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($carrier);

    return $carrierSchema;
}

it('throws error if carrier is not set', function () {
    $carrierSchema = Pdk::get(CarrierSchema::class);

    $carrierSchema->getSchema();
})->throws(BadMethodCallException::class);

it('can be digital stamp', function () {
    expect(createSchema()->canBeDigitalStamp())->toBeTrue();
});

it('can be letter', function () {
    expect(createSchema()->canBeLetter())->toBeTrue();
});

it('can be mailbox', function () {
    expect(createSchema()->canBeMailbox())->toBeTrue();
});

it('can be package', function () {
    expect(createSchema()->canBePackage())->toBeTrue();
});
it('can be package_small', function () {
    expect(createSchema()->canBePackageSmall())->toBeTrue();
});

it('can have age check', function () {
    expect(createSchema()->canHaveAgeCheck())->toBeTrue();
});

it('can have direct return', function () {
    expect(createSchema()->canHaveDirectReturn())->toBeTrue();
});

it('can have standard delivery', function () {
    expect(createSchema()->canHaveStandardDelivery())->toBeTrue();
});

it('can have evening delivery', function () {
    expect(createSchema()->canHaveEveningDelivery())->toBeTrue();
});

it('can have hide sender', function () {
    expect(createSchema()->canHaveHideSender())->toBeTrue();
});

it('can have insurance', function () {
    expect(createSchema()->canHaveInsurance())->toBeTrue();
});

it('can have large format', function () {
    expect(createSchema()->canHaveLargeFormat())->toBeTrue();
});

it('can have morning delivery', function () {
    expect(createSchema()->canHaveMorningDelivery())->toBeTrue();
});

it('can have multi collo', function () {
    expect(createSchema()->canHaveMultiCollo())->toBeTrue();
});

it('can have only recipient', function () {
    expect(createSchema()->canHaveOnlyRecipient())->toBeTrue();
});

it('can have pickup', function () {
    expect(createSchema()->canHavePickup())->toBeTrue();
});

it('can have same day delivery', function () {
    expect(createSchema()->canHaveSameDayDelivery())->toBeTrue();
});

it('can have signature', function () {
    expect(createSchema()->canHaveSignature())->toBeTrue();
});

it('can have weight', function () {
    expect(createSchema()->canHaveWeight(100))->toBeTrue();
});
