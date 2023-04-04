<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

const EXAMPLE_PRODUCT = [
    'externalIdentifier' => '123',
    'weight'             => 4000,
    'settings'           => [],
];

beforeEach(function () {
    $this->pdk = PdkFactory::create(MockPdkConfig::create());
});

it('has correct default values', function () {
    $product = new PdkProduct(EXAMPLE_PRODUCT);

    expect($product)
        ->toBeInstanceOf(PdkProduct::class)
        ->and(Arr::dot($product->toArray()))
        ->toEqual([
            'ean'                             => null,
            'name'                            => null,
            'settings.countryOfOrigin'        => 'NL',
            'settings.customsCode'            => '0000',
            'settings.disableDeliveryOptions' => -1,
            'settings.dropOffDelay'           => 0,
            'settings.exportAgeCheck'         => -1,
            'settings.exportInsurance'        => -1,
            'settings.exportLargeFormat'      => -1,
            'settings.fitInMailbox'           => 0,
            'settings.packageType'            => 'package',
            'externalIdentifier'              => '123',
            'weight'                          => 4000,
            'sku'                             => null,
            'isDeliverable'                   => null,
            'price'                           => null,
            'length'                          => 0,
            'width'                           => 0,
            'height'                          => 0,
            'settings.id'                     => 'product',
            'settings.exportOnlyRecipient'    => -1,
            'settings.exportReturn'           => -1,
            'settings.exportSignature'        => -1,
        ]);
});

it('updates product settings', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockProductRepository $repository */
    $repository = $this->pdk->get(ProductRepositoryInterface::class);

    $product = $repository->getProduct('123');

    $product->fill([
        'settings' => [
            'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'customsCode' => '42069',
        ],
    ]);

    $settings = $repository->getProductSettings('123');

    expect($settings->packageType)
        ->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
        ->and($settings->customsCode)
        ->toBe('42069');
});
