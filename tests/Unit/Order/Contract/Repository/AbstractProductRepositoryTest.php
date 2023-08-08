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
            'ean'                                   => null,
            'name'                                  => null,
            'parent'                                => null,
            'settings.countryOfOrigin'              => 'NL',
            'settings.customsCode'                  => '0000',
            'settings.disableDeliveryOptions'       => -1,
            'settings.dropOffDelay'                 => 0,
            'settings.exportAgeCheck'               => -1,
            'settings.exportInsurance'              => -1,
            'settings.exportLargeFormat'            => -1,
            'settings.fitInMailbox'                 => 0,
            'settings.packageType'                  => 'package',
            'externalIdentifier'                    => '123',
            'weight'                                => 4000,
            'sku'                                   => null,
            'isDeliverable'                         => null,
            'price'                                 => null,
            'length'                                => 0,
            'width'                                 => 0,
            'height'                                => 0,
            'settings.id'                           => 'product',
            'settings.exportOnlyRecipient'          => -1,
            'settings.exportReturn'                 => -1,
            'settings.exportSignature'              => -1,
            'settings.exportHideSender'             => -1,
            'mergedSettings.id'                     => 'product',
            'mergedSettings.countryOfOrigin'        => 'NL',
            'mergedSettings.customsCode'            => '0000',
            'mergedSettings.disableDeliveryOptions' => -1,
            'mergedSettings.dropOffDelay'           => 0,
            'mergedSettings.exportAgeCheck'         => -1,
            'mergedSettings.exportHideSender'       => -1,
            'mergedSettings.exportInsurance'        => -1,
            'mergedSettings.exportLargeFormat'      => -1,
            'mergedSettings.exportOnlyRecipient'    => -1,
            'mergedSettings.exportReturn'           => -1,
            'mergedSettings.exportSignature'        => -1,
            'mergedSettings.fitInMailbox'           => 0,
            'mergedSettings.packageType'            => 'package',
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
