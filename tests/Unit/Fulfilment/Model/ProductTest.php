<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Model\Product;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('creates fulfilment product from pdk product', function (array $input) {
    $pdkProduct = new PdkProduct($input);
    $product    = Product::fromPdkProduct($pdkProduct);

    expect($product)->toBeInstanceOf(Product::class);
    assertMatchesJsonSnapshot(json_encode($product->toArray()));
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
    assertMatchesJsonSnapshot(json_encode($product->toArray()));
});
