<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Psr\Log\LoggerInterface;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('correctly transforms deprecated fields', function (array $input, array $output) {
    $address = new Address($input);

    expect(Utils::filterNull($address->toArray()))->toBe($output);
})->with([
    'full_street' => [
        'input'  => [
            'full_street' => 'street 123 b',
        ],
        'output' => [
            'street'     => 'street 123 b',
            'isBusiness' => false,
        ],
    ],
    'address1' => [
        'input'  => [
            'address1' => 'Wegstraat 2F',
        ],
        'output' => [
            'street'     => 'Wegstraat 2F',
            'isBusiness' => false,
        ],
    ],

    'address2' => [
        'input'  => [
            'address2' => 'Wegstraat 2',
        ],
        'output' => [
            'street'     => 'Wegstraat 2',
            'isBusiness' => false,
        ],
    ],

    'address1 and address2' => [
        'input'  => [
            'address1'      => 'street 123',
            'address2'        => 'b',
        ],
        'output' => [
            'street'        => 'street 123 b',
            'streetAdditionalInfo' => 'b',
            'isBusiness'    => false,
        ],
    ],
]);

it('derives isBusiness from a company name', function () {
    $address = new Address(['cc' => 'NL', 'company' => 'Acme B.V.']);

    expect($address->isBusiness)->toBeTrue();
});

it('treats an empty or whitespace-only company as not a business', function (?string $company) {
    $address = new Address(['cc' => 'NL', 'company' => $company]);

    expect($address->isBusiness)->toBeFalse();
})->with([
    'empty string'    => [''],
    'whitespace only' => ['   '],
    'null'            => [null],
]);

it('defaults isBusiness to false when no company is provided', function () {
    $address = new Address(['cc' => 'NL']);

    expect($address->isBusiness)->toBeFalse();
});

it('derives isBusiness on a bare Address without storing or serialising the company', function () {
    $address = new Address(['cc' => 'NL', 'company' => 'Acme B.V.']);

    expect($address->isBusiness)->toBeTrue()
        ->and($address->company)->toBeNull()
        ->and($address->toArray())
        ->toHaveKey('isBusiness', true)
        ->not->toHaveKey('company');
});

it('keeps an explicit isBusiness flag when no company is present (rehydration)', function () {
    $address = new Address(['cc' => 'NL', 'isBusiness' => true]);

    expect($address->isBusiness)->toBeTrue();
});

it('lets a present company override a stale isBusiness flag', function () {
    $business = new Address(['cc' => 'NL', 'company' => 'Acme B.V.', 'isBusiness' => false]);
    $consumer = new Address(['cc' => 'NL', 'company' => '', 'isBusiness' => true]);

    expect($business->isBusiness)->toBeTrue()
        ->and($consumer->isBusiness)->toBeFalse();
});

it('does not log deprecation warnings for address1 or address2', function (array $input) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    $logger->clear();

    new Address($input);

    expect($logger->getLogs('notice'))->toBe([]);
})->with([
    'address1' => [
        'input' => [
            'address1' => 'Wegstraat 2F',
        ],
    ],
    'address2' => [
        'input' => [
            'address2' => 'Wegstraat 2',
        ],
    ],
    'address1 and address2' => [
        'input' => [
            'address1' => 'street 123',
            'address2' => 'b',
        ],
    ],
]);
