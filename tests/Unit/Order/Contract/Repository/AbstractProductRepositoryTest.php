<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

const EXAMPLE_PRODUCT = [
    'externalIdentifier' => '123',
    'weight'             => 4000,
    'settings'           => [],
];

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
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository $repository */
    $repository = Pdk::get(PdkProductRepositoryInterface::class);

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