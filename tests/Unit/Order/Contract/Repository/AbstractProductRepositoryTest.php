<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentResponsesDeliveryOptionsPackageTypeV2;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('updates product settings', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository $repository */
    $repository = Pdk::get(PdkProductRepositoryInterface::class);

    $product = $repository->getProduct('123');

    $product->fill([
        'settings' => [
            'packageType' => ShipmentResponsesDeliveryOptionsPackageTypeV2::MAILBOX,
            'customsCode' => '42069',
        ],
    ]);

    $settings = $repository->getProductSettings('123');

    expect($settings->packageType)
        ->toBe(ShipmentResponsesDeliveryOptionsPackageTypeV2::MAILBOX)
        ->and($settings->customsCode)
        ->toBe('42069');
});
