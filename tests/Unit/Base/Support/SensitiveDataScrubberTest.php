<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

it('masks scalar values of sensitive keys', function () {
    $result = SensitiveDataScrubber::scrubArray(['access_token' => 'abc123', 'page' => 2]);

    expect($result['access_token'])->toBe('***')
        ->and($result['page'])->toBe(2);
});

it('masks every value beneath a sensitive key that holds an array', function () {
    $result = SensitiveDataScrubber::scrubArray(['token' => ['secret']]);

    expect($result['token'])->toBe(['***']);
});

it('masks all leaves beneath a sensitive key regardless of the child key names', function () {
    $result = SensitiveDataScrubber::scrubArray([
        'customer' => ['id' => 5, 'orders' => [['reference' => 'A'], ['reference' => 'B']]],
    ]);

    expect($result['customer']['id'])->toBe('***')
        ->and($result['customer']['orders'][0]['reference'])->toBe('***')
        ->and($result['customer']['orders'][1]['reference'])->toBe('***');
});

it('leaves non-sensitive keys untouched while masking sensitive ones at the same level', function () {
    $result = SensitiveDataScrubber::scrubArray(['data' => ['page' => 2, 'email' => 'a@b.test']]);

    expect($result['data']['page'])->toBe(2)
        ->and($result['data']['email'])->toBe('***');
});
