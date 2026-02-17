<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets configuration factory values', function (string $key, $value) {
    expect(Pdk::get($key))->toBe($value);
})->with([
    ['deliveryOptionsCdnUrlCss', 'https://cdn.jsdelivr.net/npm/@myparcel-dev/delivery-options@6/dist/style.css'],
    ['deliveryOptionsCdnUrlJs', 'https://cdn.jsdelivr.net/npm/@myparcel-dev/delivery-options@6/dist/myparcel.js'],
    ['deliveryOptionsCdnUrlJsLib', 'https://cdn.jsdelivr.net/npm/@myparcel-dev/delivery-options@6/dist/myparcel.lib.js'],
]);
