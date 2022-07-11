<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Data\CountryCodes;

it('initializes be address', function () {
    $address = new Address([
        'boxNumber'  => 'a',
        'country'    => CountryCodes::CC_BE,
        'number'     => 16,
        'person'     => 'Bruno',
        'postalCode' => '2000',
        'street'     => 'Adriaan Brouwerstraat',
        'city'       => 'Antwerpen',
    ]);

    expect($address->number)->toBe(16);
});

it('sets from full street without country', function () {
    $address = new Address([
        'person'     => 'Frank',
        'postalCode' => '2132JE',
        'fullStreet' => 'Antareslaan 31a',
        'city'       => 'Hoofddorp',
    ]);
})->throws(InvalidArgumentException::class);

it('sets from full street', function () {
    $address = new Address([
        'country'    => CountryCodes::CC_NL,
        'person'     => 'Frank',
        'postalCode' => '2132JE',
        'fullStreet' => 'Antareslaan 31a',
        'city'       => 'Hoofddorp',
    ]);

    expect($address->number)
        ->toBe(31)
        ->and($address->street)
        ->toBe('Antareslaan')
        ->and($address->numberSuffix)
        ->toBe('a');
});
