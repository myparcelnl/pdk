<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Mocks\MockSdkInheritingModel;
use MyParcelNL\Pdk\Tests\Mocks\MockSdkModel;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsInsuranceOptionV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCollo;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsInsuranceBaseInsuranceV2InsuredAmount;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesMoney;
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

// ----- Typed-property hydration via openAPITypes (SdkBackedModel::setAttribute) -----

it('hydrates a raw array into a ModelInterface for a typed SDK property', function () {
    $carrier = new Carrier(['collo' => ['max' => 7]]);

    expect($carrier->collo)
        ->toBeInstanceOf(RefCapabilitiesResponseCollo::class)
        ->and($carrier->collo->getMax())->toBe(7);
});

it('hydrates nested models recursively when the outer model is also an array', function () {
    $carrier = new Carrier([
        'options' => [
            // attributeMap for requiresSignature uses camelCase JSON key 'requiresSignature'
            'requiresSignature' => ['isSelectedByDefault' => true, 'isRequired' => false],
        ],
    ]);

    expect($carrier->options)
        ->toBeInstanceOf(RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::class)
        ->and($carrier->options->getRequiresSignature())
        ->toBeInstanceOf(RefCapabilitiesContractDefinitionsResponseOptionsOptionV2::class)
        ->and($carrier->options->getRequiresSignature()->getIsSelectedByDefault())->toBeTrue()
        ->and($carrier->options->getRequiresSignature()->getIsRequired())->toBeFalse();
});

it('passes an already-hydrated ModelInterface instance through without re-hydrating', function () {
    $collo = new RefCapabilitiesResponseCollo(['max' => 3]);

    $carrier = new Carrier(['collo' => $collo]);

    expect($carrier->collo)->toBe($collo);
});

it('passes an already-hydrated ModelInterface through when set via setAttribute', function () {
    $collo   = new RefCapabilitiesResponseCollo(['max' => 9]);
    $carrier = new Carrier();

    $carrier->collo = $collo;

    expect($carrier->collo)->toBe($collo);
});

it('round-trips a carrier through toArray and back without corrupting typed properties', function () {
    $original = new Carrier(['collo' => ['max' => 4]]);

    $array  = $original->toArray();
    $reload = new Carrier($array);

    expect($reload->collo)
        ->toBeInstanceOf(RefCapabilitiesResponseCollo::class)
        ->and($reload->collo->getMax())->toBe(4);
});

it('hydrates insurance option with nested insuredAmount from plain arrays', function () {
    $carrier = new Carrier([
        'options' => [
            'insurance' => [
                'insuredAmount' => [
                    'default' => ['currency' => 'EUR', 'amount' => 0],
                    'min'     => ['currency' => 'EUR', 'amount' => 0],
                    'max'     => ['currency' => 'EUR', 'amount' => 500000],
                ],
            ],
        ],
    ]);

    $options = $carrier->options;
    expect($options)->toBeInstanceOf(RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::class);

    $insurance = $options->getInsurance();
    expect($insurance)->toBeInstanceOf(RefCapabilitiesContractDefinitionsResponseOptionsInsuranceOptionV2::class);

    $insuredAmount = $insurance->getInsuredAmount();
    expect($insuredAmount)->toBeInstanceOf(RefCapabilitiesSharedOptionsInsuranceBaseInsuranceV2InsuredAmount::class);

    expect($insuredAmount->getMax())->toBeInstanceOf(RefTypesMoney::class)
        ->and($insuredAmount->getMax()->getAmount())->toBe(500000)
        ->and($insuredAmount->getMin())->toBeInstanceOf(RefTypesMoney::class)
        ->and($insuredAmount->getMin()->getAmount())->toBe(0)
        ->and($insuredAmount->getDefault())->toBeInstanceOf(RefTypesMoney::class)
        ->and($insuredAmount->getDefault()->getAmount())->toBe(0);
});
