<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('derives isBusiness from company at construction and keeps the company', function () {
    $contact = new ContactDetails(['company' => 'Acme B.V.']);

    expect($contact->isBusiness)->toBeTrue()
        ->and($contact->company)->toBe('Acme B.V.');
});

it('defaults isBusiness to false without a company', function () {
    $contact = new ContactDetails(['person' => 'Jane Doe']);

    expect($contact->isBusiness)->toBeFalse();
});

it('updates isBusiness when the company is set after construction', function () {
    $contact = new ContactDetails(['person' => 'Jane Doe']);
    expect($contact->isBusiness)->toBeFalse();

    $contact->company = 'Acme B.V.';

    expect($contact->isBusiness)->toBeTrue();
});

it('clears isBusiness when the company is cleared after construction', function (string $cleared) {
    $contact = new ContactDetails(['company' => 'Acme B.V.']);
    expect($contact->isBusiness)->toBeTrue();

    $contact->company = $cleared;

    expect($contact->isBusiness)->toBeFalse();
})->with([
    'empty string'    => [''],
    'whitespace only' => ['   '],
]);

it('uses an explicit isBusiness only when no company is present', function () {
    $contact = new ContactDetails(['person' => 'Jane Doe', 'isBusiness' => true]);

    expect($contact->isBusiness)->toBeTrue();
});

it('lets the company dictate the flag even when a stale isBusiness is supplied', function () {
    $contact = new ContactDetails(['company' => 'Acme B.V.', 'isBusiness' => false]);

    expect($contact->isBusiness)->toBeTrue();
});

it('updates isBusiness when the company is changed via fill', function () {
    $contact = new ContactDetails(['person' => 'Jane Doe']);

    $contact->fill(['company' => 'Acme B.V.']);

    expect($contact->isBusiness)->toBeTrue();
});

it('serialises isBusiness alongside the company', function () {
    $contact = new ContactDetails(['company' => 'Acme B.V.']);

    expect($contact->toArray())
        ->toHaveKey('isBusiness', true)
        ->toHaveKey('company', 'Acme B.V.');
});
