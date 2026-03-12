<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Mocks\MockSdkInheritingModel;
use MyParcelNL\Pdk\Tests\Mocks\MockSdkModel;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function expect;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('model', 'sdk');

usesShared(new UsesMockPdkInstance());

it('reads SDK model properties through the PDK model', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'age'        => 30,
        'name'       => 'sdk-name',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'sdkData' => $sdkModel,
    ]);

    // SDK properties accessible through camelCase
    expect($model->firstName)->toBe('John')
        ->and($model->lastName)->toBe('Doe')
        ->and($model->age)->toBe(30);
});

it('can be constructed from a raw SDK property array', function () {
    $model = new MockSdkInheritingModel([
        'title'     => 'Ms.',
        'firstName' => 'Jane',
        'lastName'  => 'Doe',
        'age'       => 25,
    ]);

    expect($model->firstName)->toBe('Jane')
        ->and($model->lastName)->toBe('Doe')
        ->and($model->age)->toBe(25)
        ->and($model->title)->toBe('Ms.');
});

it('exposes the underlying SDK model via getSdkModel()', function () {
    $sdkModel = new MockSdkModel(['first_name' => 'John']);
    $model    = new MockSdkInheritingModel(['sdkModel' => $sdkModel]);

    expect($model->getSdkModel())->toBe($sdkModel);
});

it('gives priority to native PDK attributes over SDK properties', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'name'       => 'sdk-name-value',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Dr.',
        'name'    => 'pdk-name-value',
        'sdkData' => $sdkModel,
    ]);

    // Native PDK 'name' attribute takes priority over SDK model's 'name'
    expect($model->name)->toBe('pdk-name-value')
        ->and($model->firstName)->toBe('John');
});

it('writes SDK model properties through the PDK model', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'last_name'  => 'Doe',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'sdkData' => $sdkModel,
    ]);

    $model->firstName = 'Jane';
    $model->lastName  = 'Smith';

    expect($model->firstName)->toBe('Jane')
        ->and($model->lastName)->toBe('Smith')
        // Verify it was written through to the SDK model
        ->and($sdkModel->getFirstName())->toBe('Jane')
        ->and($sdkModel->getLastName())->toBe('Smith');
});

it('merges SDK properties at root level in toArray()', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'age'        => 30,
        'name'       => 'sdk-name',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'name'    => 'pdk-name',
        'sdkData' => $sdkModel,
    ]);

    $array = $model->toArray();

    // SDK properties should be at root level
    expect($array)->toHaveKey('firstName')
        ->and($array)->toHaveKey('lastName')
        ->and($array)->toHaveKey('age')
        ->and($array['firstName'])->toBe('John')
        ->and($array['lastName'])->toBe('Doe')
        ->and($array['age'])->toBe(30);

    // Native PDK 'name' should win over SDK 'name'
    expect($array['name'])->toBe('pdk-name');

    // The raw SDK model attribute key should be removed
    expect($array)->not->toHaveKey('sdkData');
});

it('handles absent SDK model gracefully', function () {
    $model = new MockSdkInheritingModel([
        'title' => 'Mr.',
        'name'  => 'test',
        // No non-native keys → $sdkModel stays null
    ]);

    expect($model->firstName)->toBeNull()
        ->and($model->getSdkModel())->toBeNull()
        ->and($model->title)->toBe('Mr.')
        ->and($model->toArray())->toHaveKey('title');
});

it('supports snake_case output in toArray with flags', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'last_name'  => 'Doe',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'sdkData' => $sdkModel,
    ]);

    $array = $model->toSnakeCaseArray();

    expect($array)->toHaveKey('first_name')
        ->and($array)->toHaveKey('last_name')
        ->and($array['first_name'])->toBe('John')
        ->and($array['last_name'])->toBe('Doe');
});

it('only includes non-null SDK properties in toArray', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
        'last_name'  => null,
        'age'        => null,
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'sdkData' => $sdkModel,
    ]);

    $array = $model->toArray();

    expect($array)->toHaveKey('firstName')
        ->and($array)->not->toHaveKey('lastName')
        ->and($array)->not->toHaveKey('age');
});

it('can set native attributes without affecting SDK model', function () {
    $sdkModel = new MockSdkModel([
        'first_name' => 'John',
    ]);

    $model = new MockSdkInheritingModel([
        'title'   => 'Mr.',
        'sdkData' => $sdkModel,
    ]);

    $model->title = 'Dr.';

    expect($model->title)->toBe('Dr.')
        ->and($model->firstName)->toBe('John');
});
