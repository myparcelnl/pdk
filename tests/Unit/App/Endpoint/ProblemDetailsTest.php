<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Endpoint\ProblemDetails;

/**
 * Tests for RFC 9457 compliant ProblemDetails value object.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9457.html
 */

it('creates a minimal problem details object', function () {
    $problemDetails = new ProblemDetails();

    expect($problemDetails->getType())->toBeNull();
    expect($problemDetails->getTitle())->toBeNull();
    expect($problemDetails->getStatus())->toBeNull();
    expect($problemDetails->getDetail())->toBeNull();
    expect($problemDetails->getInstance())->toBeNull();
});

it('creates a problem details object with all standard members', function () {
    $problemDetails = new ProblemDetails(
        'https://example.com/problems/out-of-credit',
        'You do not have enough credit.',
        403,
        'Your current balance is 30, but that costs 50.',
        '/account/12345/transactions/abc'
    );

    expect($problemDetails->getType())->toBe('https://example.com/problems/out-of-credit');
    expect($problemDetails->getTitle())->toBe('You do not have enough credit.');
    expect($problemDetails->getStatus())->toBe(403);
    expect($problemDetails->getDetail())->toBe('Your current balance is 30, but that costs 50.');
    expect($problemDetails->getInstance())->toBe('/account/12345/transactions/abc');
});

it('converts to array with only required members when minimal', function () {
    $problemDetails = new ProblemDetails();

    $array = $problemDetails->toArray();

    // RFC 9457: type and status are always present (even if null)
    expect($array)->toHaveKeys(['type', 'status']);
    expect($array)->not->toHaveKeys(['title', 'detail', 'instance']);
    expect($array['type'])->toBeNull();
    expect($array['status'])->toBeNull();
});

it('converts to array with all standard members', function () {
    $problemDetails = new ProblemDetails(
        'https://example.com/problems/validation-error',
        'Validation Error',
        400,
        'The request body contained invalid data.',
        '/requests/abc123'
    );

    $array = $problemDetails->toArray();

    expect($array)->toBe([
        'type'     => 'https://example.com/problems/validation-error',
        'status'   => 400,
        'title'    => 'Validation Error',
        'detail'   => 'The request body contained invalid data.',
        'instance' => '/requests/abc123',
    ]);
});

it('omits optional members when null', function () {
    $problemDetails = new ProblemDetails(
        'https://example.com/problems/not-found',
        null,
        404,
        null,
        null
    );

    $array = $problemDetails->toArray();

    expect($array)->toBe([
        'type'   => 'https://example.com/problems/not-found',
        'status' => 404,
    ]);
});

it('adds extension members immutably', function () {
    $original = new ProblemDetails(
        'https://example.com/problems/out-of-credit',
        'You do not have enough credit.',
        403
    );

    $withBalance = $original->with('balance', 30);
    $withAccounts = $withBalance->with('accounts', ['/account/12345', '/account/67890']);

    // Original should not be modified
    expect($original->toArray())->not->toHaveKey('balance');
    expect($original->toArray())->not->toHaveKey('accounts');

    // First extension
    expect($withBalance->toArray())->toHaveKey('balance');
    expect($withBalance->toArray()['balance'])->toBe(30);
    expect($withBalance->toArray())->not->toHaveKey('accounts');

    // Second extension should include both
    expect($withAccounts->toArray())->toHaveKey('balance');
    expect($withAccounts->toArray())->toHaveKey('accounts');
    expect($withAccounts->toArray()['accounts'])->toBe(['/account/12345', '/account/67890']);
});

it('includes extension members in array output', function () {
    $problemDetails = new ProblemDetails(
        'https://example.com/problems/out-of-credit',
        'You do not have enough credit.',
        403,
        'Your current balance is 30, but that costs 50.'
    );

    $withExtensions = $problemDetails
        ->with('balance', 30)
        ->with('cost', 50);

    $array = $withExtensions->toArray();

    expect($array)->toBe([
        'type'    => 'https://example.com/problems/out-of-credit',
        'status'  => 403,
        'title'   => 'You do not have enough credit.',
        'detail'  => 'Your current balance is 30, but that costs 50.',
        'balance' => 30,
        'cost'    => 50,
    ]);
});

it('throws exception when flags are passed to toArray', function () {
    $problemDetails = new ProblemDetails('about:blank', 'Error', 500);

    $problemDetails->toArray(JSON_PRETTY_PRINT);
})->throws(InvalidArgumentException::class, 'Flags are not supported for ProblemDetails toArray method.');

it('supports various extension member value types', function () {
    $problemDetails = new ProblemDetails('about:blank', 'Error', 400);

    $withExtensions = $problemDetails
        ->with('string_value', 'hello')
        ->with('int_value', 42)
        ->with('float_value', 3.14)
        ->with('bool_value', true)
        ->with('null_value', null)
        ->with('array_value', ['a', 'b', 'c'])
        ->with('nested_value', ['key' => 'value']);

    $array = $withExtensions->toArray();

    expect($array['string_value'])->toBe('hello');
    expect($array['int_value'])->toBe(42);
    expect($array['float_value'])->toBe(3.14);
    expect($array['bool_value'])->toBe(true);
    expect($array['null_value'])->toBeNull();
    expect($array['array_value'])->toBe(['a', 'b', 'c']);
    expect($array['nested_value'])->toBe(['key' => 'value']);
});

it('can overwrite extension members', function () {
    $problemDetails = new ProblemDetails('about:blank', 'Error', 400);

    $first = $problemDetails->with('retryAfter', 30);
    $second = $first->with('retryAfter', 60);

    expect($first->toArray()['retryAfter'])->toBe(30);
    expect($second->toArray()['retryAfter'])->toBe(60);
});

it('implements Arrayable interface', function () {
    $problemDetails = new ProblemDetails();

    expect($problemDetails)->toBeInstanceOf(\MyParcelNL\Pdk\Base\Contract\Arrayable::class);
});
