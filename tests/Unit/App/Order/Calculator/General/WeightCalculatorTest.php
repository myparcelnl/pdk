<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\WeightCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

afterEach(function () {
    $this->resetServices();
});

it('calculates weight', function (string $packageType, $initialWeight, $totalWeight, $customsDeclarationWeight) {
    $reset = mockPdkProperty('orderCalculators', [WeightCalculator::class]);

    factory(OrderSettings::class)
        ->withEmptyParcelWeight(1000)
        ->withEmptyMailboxWeight(500)
        ->withEmptyDigitalStampWeight(10)
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withPackageType($packageType))
        ->withPhysicalProperties(factory(PhysicalProperties::class)->withWeight($initialWeight))
        ->withCustomsDeclaration(factory(CustomsDeclaration::class))
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->physicalProperties->weight)
        ->toBe($totalWeight)
        ->and($newOrder->customsDeclaration->weight)
        ->toBe($customsDeclarationWeight);

    $reset();
})
    ->with([
        'package of type package'       => [
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            1000,
            2000,
            1000,
        ],
        'package of type mailbox'       => [
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            1000,
            1500,
            500,
        ],
        'package of type digital stamp' => [
            DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
            10,
            20,
            10,
        ],
        'package of type letter'        => [
            DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
            10,
            10,
            0,
        ],
    ]);
