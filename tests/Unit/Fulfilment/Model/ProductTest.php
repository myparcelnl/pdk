<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());
it('creates fulfilment product from pdk product', function (array $input) {
    $pdkProduct = new PdkProduct($input);
    $product    = Product::fromPdkProduct($pdkProduct);

    expect($product)->toBeInstanceOf(Product::class);
    assertMatchesJsonSnapshot(json_encode($product->toArray(), JSON_THROW_ON_ERROR));
})->with([
    'empty product'            => [[]],
    'product with all options' => [
        [
            'sku'    => 'ABC123456',
            'ean'    => '1234567890123',
            'name'   => 'Product name',
            'weight' => 1000,
        ],
    ],
]);

it('returns empty fulfilment product when no pdk product is passed', function () {
    $product = Product::fromPdkProduct(null);
    expect($product)->toBeInstanceOf(Product::class);
    assertMatchesJsonSnapshot(json_encode($product->toArray(), JSON_THROW_ON_ERROR));
});
