<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\MockPdkProductRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

it('formats label description', function (string $labelDescription, string $output) {
    /** @var MockPdkProductRepository $repository */
    $repository = Pdk::get(PdkProductRepositoryInterface::class);

    $order = new PdkOrder([
        'externalIdentifier' => '123',
        'deliveryOptions'    => [
            'shipmentOptions' => [
                'labelDescription' => $labelDescription,
            ],
        ],
        'lines'              => [
            [
                'quantity' => 2,
                'product'  => $repository->getProduct('456'),
            ],
            [
                'quantity' => 3,
                'product'  => $repository->getProduct('789'),
            ],
        ],
        'notes'              => [
            [
                'author' => OrderNote::AUTHOR_CUSTOMER,
                'note'   => 'Hello',
            ],
        ],
    ]);

    /** @var ShipmentOptionsServiceInterface $service */
    $service  = Pdk::get(ShipmentOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->labelDescription)->toBe($output);
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
