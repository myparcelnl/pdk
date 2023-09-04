<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\LabelDescriptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createOrder(string $labelDescription): PdkOrder
{
    return factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(['shipmentOptions' => ['labelDescription' => $labelDescription]])
        ->withLines([
            factory(PdkOrderLine::class)
                ->withQuantity(2)
                ->withProduct('456'),
            factory(PdkOrderLine::class)
                ->withQuantity(3)
                ->withProduct('789'),
        ])
        ->withNotes([
            factory(PdkOrderNote::class)
                ->withAuthor(OrderNote::AUTHOR_CUSTOMER)
                ->withNote('Hello'),
        ])
        ->store()
        ->make();
}

it('formats label description', function (string $labelDescription, string $output) {
    $reset = mockPdkProperty('orderCalculators', [LabelDescriptionCalculator::class]);
    $order = createOrder($labelDescription);

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->labelDescription)->toBe($output);

    $reset();
})
    ->with([
        'order with label description CUSTOMER_NOTE'         => [
            'input'  => 'CUSTOMER_NOTE: [CUSTOMER_NOTE]',
            'output' => 'CUSTOMER_NOTE: Hello',
        ],
        'order with label description ORDER_ID'              => [
            'input'  => 'ORDER_ID: [ORDER_ID]',
            'output' => 'ORDER_ID: 123',
        ],
        'order with label description PRODUCT_ID'            => [
            'input'  => 'PRODUCT_ID: [PRODUCT_ID]',
            'output' => 'PRODUCT_ID: 456, 789',
        ],
        'order with label description PRODUCT_NAME'          => [
            'input'  => 'PRODUCT_NAME: [PRODUCT_NAME]',
            'output' => 'PRODUCT_NAME: Apple, Banana',
        ],
        'order with label description PRODUCT_SKU'           => [
            'input'  => 'PRODUCT_SKU: [PRODUCT_SKU]',
            'output' => 'PRODUCT_SKU: A-456, A-789',
        ],
        'order with label description PRODUCT_EAN'           => [
            'input'  => 'PRODUCT_EAN: [PRODUCT_EAN]',
            'output' => 'PRODUCT_EAN: 212444',
        ],
        'order with label description PRODUCT_QTY'           => [
            'input'  => 'PRODUCT_QTY: [PRODUCT_QTY]',
            'output' => 'PRODUCT_QTY: 5',
        ],
        'order with multiple label description placeholders' => [
            'input'  => '[CUSTOMER_NOTE] | [ORDER_ID] | [PRODUCT_ID] | [PRODUCT_NAME] | [PRODUCT_SKU] | [PRODUCT_EAN] | [PRODUCT_QTY]',
            'output' => 'Hello | 123 | 456, 789 | Apple, Banana | A-456, A-789 | 212444 | 5',
        ],
    ]);
