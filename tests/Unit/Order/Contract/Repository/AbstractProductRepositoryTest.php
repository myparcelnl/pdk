<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

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
