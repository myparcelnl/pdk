<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUndefinedFieldInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;

it('initializes be address', function () {
    $address = new Address([
        'boxNumber'  => 'a',
        'cc'         => CountryCodes::CC_BE,
        'number'     => 16,
        'postalCode' => '2000',
        'street'     => 'Adriaan Brouwerstraat',
        'city'       => 'Antwerpen',
    ]);

    expect($address->number)->toBe('16');
});

it('sets from full street without country', function () {
    new Address([
        'postalCode' => '2132JE',
        'fullStreet' => 'Antareslaan 31a',
        'city'       => 'Hoofddorp',
    ]);
})->throws(InvalidArgumentException::class);

it('sets from full street', function () {
    $address = new Address([
        'cc'         => CountryCodes::CC_NL,
        'postalCode' => '2132JE',
        'fullStreet' => 'Antareslaan 31a',
        'city'       => 'Hoofddorp',
    ]);

    expect($address->number)
        ->toBe('31')
        ->and($address->street)
        ->toBe('Antareslaan')
        ->and($address->numberSuffix)
        ->toBe('a');
});
