<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

it('limits string length', function (string $string, int $length, string $end, string $result) {
    $limited = Str::limit($string, $length, $end);

    expect($limited)
        ->toBe($result)
        ->and(strlen($limited))
        ->toBeLessThanOrEqual($length);
})->with([
    ['some long string', 10, '...', 'some lo...'],
    ['short enough', 40, '_-.', 'short enough'],
    ['broccoli', 5, '~', 'broc~'],
]);

it('changes string case', function (string $string, ?int $flags, string $result) {
    expect(Str::changeCase($string, $flags))->toBe($result);
})->with([
    ['my string', null, 'myString'],
    ['my string', Str::CASE_KEBAB, 'my-string'],
    ['my string', Str::CASE_SNAKE, 'my_string'],
    ['my string', Str::CASE_STUDLY, 'MyString'],

    ['myString', null, 'myString'],
    ['myString', Str::CASE_SNAKE, 'my_string'],
    ['myString', Str::CASE_KEBAB, 'my-string'],
    ['myString', Str::CASE_STUDLY, 'MyString'],

    ['my-string', null, 'myString'],
    // Kebab can't be converted to snake
    ['my-string', Str::CASE_KEBAB, 'my-string'],
    ['my-string', Str::CASE_STUDLY, 'MyString'],

    ['my_string', null, 'myString'],
    ['my_string', Str::CASE_SNAKE, 'my_string'],
    // Snake can't be converted to kebab
    ['my_string', Str::CASE_STUDLY, 'MyString'],

    ['MyString', null, 'myString'],
    ['MyString', Str::CASE_SNAKE, 'my_string'],
    ['MyString', Str::CASE_KEBAB, 'my-string'],
    ['MyString', Str::CASE_STUDLY, 'MyString'],
]);
