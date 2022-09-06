<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Product\Repository\AbstractProductRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

const EXAMPLE_PRODUCT = [
    'sku'      => '123',
    'weight'   => 4000,
    'settings' => [],
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
            'sku'                             => '123',
            'weight'                          => 4000,
            'settings.allowOnlyRecipient'     => false,
            'settings.allowSignature'         => false,
            'settings.countryOfOrigin'        => 'NL',
            'settings.customsCode'            => '0',
            'settings.disableDeliveryOptions' => false,
            'settings.dropOffDelay'           => 0,
            'settings.exportAgeCheck'         => false,
            'settings.exportInsurance'        => false,
            'settings.exportLargeFormat'      => false,
            'settings.fitInMailbox'           => 0,
            'settings.packageType'            => 'package',
            'settings.returnShipments'        => false,
        ]);
});

it('updates product settings', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockProductRepository $repository */
    $repository = $this->pdk->get(AbstractProductRepository::class);

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
