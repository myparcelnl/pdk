<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Tests\Mocks\MockSdkModel;
use function expect;

uses()->group('model', 'sdk');

it('converts keys between PDK and SDK case', function () {
    expect(SdkModelHelper::toPdkCase('first_name'))->toBe('firstName')
        ->and(SdkModelHelper::toPdkCase('package_types'))->toBe('packageTypes')
        ->and(SdkModelHelper::toOpenApiKey('firstName'))->toBe('first_name')
        ->and(SdkModelHelper::toOpenApiKey('packageTypes'))->toBe('package_types');
});

it('builds getter map with camelCase keys', function () {
    $map = SdkModelHelper::buildGetterMap(MockSdkModel::class);

    expect($map)->toBe([
        'firstName' => 'getFirstName',
        'lastName'  => 'getLastName',
        'age'       => 'getAge',
        'name'      => 'getName',
    ]);
});

it('builds setter map with camelCase keys', function () {
    $map = SdkModelHelper::buildSetterMap(MockSdkModel::class);

    expect($map)->toBe([
        'firstName' => 'setFirstName',
        'lastName'  => 'setLastName',
        'age'       => 'setAge',
        'name'      => 'setName',
    ]);
});

it('serializes SDK model to camelCase array', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'Jane',
        'last_name'  => 'Smith',
        'age'        => 25,
        'name'       => 'test',
    ]);

    $array = SdkModelHelper::toArray($sdkModel);

    expect($array)->toBe([
        'firstName' => 'Jane',
        'lastName'  => 'Smith',
        'age'       => 25,
        'name'      => 'test',
    ]);
});

it('excludes null values from serialized array', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'Jane',
    ]);

    $array = SdkModelHelper::toArray($sdkModel);

    expect($array)->toHaveKey('firstName')
        ->and($array)->not->toHaveKey('lastName')
        ->and($array)->not->toHaveKey('age');
});
