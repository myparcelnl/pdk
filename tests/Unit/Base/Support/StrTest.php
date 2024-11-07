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
