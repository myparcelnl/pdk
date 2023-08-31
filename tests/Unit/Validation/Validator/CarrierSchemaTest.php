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

function setup(): CarrierSchema
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
    expect(setup()->canBeDigitalStamp())->toBeTrue();
});

it('can be letter', function () {
    expect(setup()->canBeLetter())->toBeTrue();
});

it('can be mailbox', function () {
    expect(setup()->canBeMailbox())->toBeTrue();
});

it('can be package', function () {
    expect(setup()->canBePackage())->toBeTrue();
});

it('can have age check', function () {
    expect(setup()->canHaveAgeCheck())->toBeTrue();
});

it('can have date', function () {
    expect(setup()->canHaveDate())->toBeTrue();
});

it('can have direct return', function () {
    expect(setup()->canHaveDirectReturn())->toBeTrue();
});

it('can have evening delivery', function () {
    expect(setup()->canHaveEveningDelivery())->toBeTrue();
});

it('can have hide sender', function () {
    expect(setup()->canHaveHideSender())->toBeTrue();
});

it('can have insurance', function () {
    expect(setup()->canHaveInsurance())->toBeTrue();
});

it('can have large format', function () {
    expect(setup()->canHaveLargeFormat())->toBeTrue();
});

it('can have morning delivery', function () {
    expect(setup()->canHaveMorningDelivery())->toBeTrue();
});

it('can have multi collo', function () {
    expect(setup()->canHaveMultiCollo())->toBeTrue();
});

it('can have only recipient', function () {
    expect(setup()->canHaveOnlyRecipient())->toBeTrue();
});

it('can have pickup', function () {
    expect(setup()->canHavePickup())->toBeTrue();
});

it('can have same day delivery', function () {
    expect(setup()->canHaveSameDayDelivery())->toBeTrue();
});

it('can have signature', function () {
    expect(setup()->canHaveSignature())->toBeTrue();
});

it('can have weight', function () {
    expect(setup()->canHaveWeight(100))->toBeTrue();
});
