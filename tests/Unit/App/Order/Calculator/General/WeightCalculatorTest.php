<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\WeightCalculator;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('calculates weight', function (
    string $packageType,
    int    $totalWeight
) {
    $reset = mockPdkProperty('orderCalculators', [WeightCalculator::class]);

    factory(OrderSettings::class)
        ->withEmptyParcelWeight(200)
        ->withEmptyMailboxWeight(100)
        ->withEmptyDigitalStampWeight(50)
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withPackageType($packageType))
        ->withLines(
            factory(PdkOrderLineCollection::class)->push(
                factory(PdkOrderLine::class)
                    ->withProduct(factory(PdkProduct::class)->withWeight(100))
            )
        )
        ->withCustomsDeclaration(factory(CustomsDeclaration::class))
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->physicalProperties->totalWeight)
        ->toBe($totalWeight)
        ->and($newOrder->customsDeclaration->weight)
        ->toBe($totalWeight);

    $reset();
})
    ->with([
        'package'       => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME, 100 + 200],
        'mailbox'       => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, 100 + 100],
        'digital stamp' => [DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME, 100],
        'letter'        => [DeliveryOptions::PACKAGE_TYPE_LETTER_NAME, 100],
    ]);
