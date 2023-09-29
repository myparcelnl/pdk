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
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createOrder($labelDescription): PdkOrder
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

it('formats label description from order', function ($labelDescription, $output) {
    $reset = mockPdkProperty('orderCalculators', [LabelDescriptionCalculator::class]);
    $order = createOrder($labelDescription);

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->labelDescription)->toEqual($output);

    $reset();
})
    ->with([
        'inherited label description'             => [
            'input'  => TriStateService::INHERIT,
            'output' => '',
        ],
        'empty label description'                 => [
            'input'  => '',
            'output' => '',
        ],
        'label description CUSTOMER_NOTE'         => [
            'input'  => 'CUSTOMER_NOTE: [CUSTOMER_NOTE]',
            'output' => 'CUSTOMER_NOTE: Hello',
        ],
        'label description ORDER_ID'              => [
            'input'  => 'ORDER_ID: [ORDER_ID]',
            'output' => 'ORDER_ID: 123',
        ],
        'label description PRODUCT_ID'            => [
            'input'  => 'PRODUCT_ID: [PRODUCT_ID]',
            'output' => 'PRODUCT_ID: 456, 789',
        ],
        'label description PRODUCT_NAME'          => [
            'input'  => 'PRODUCT_NAME: [PRODUCT_NAME]',
            'output' => 'PRODUCT_NAME: Apple, Banana',
        ],
        'label description PRODUCT_SKU'           => [
            'input'  => 'PRODUCT_SKU: [PRODUCT_SKU]',
            'output' => 'PRODUCT_SKU: A-456, A-789',
        ],
        'label description PRODUCT_EAN'           => [
            'input'  => 'PRODUCT_EAN: [PRODUCT_EAN]',
            'output' => 'PRODUCT_EAN: 212444',
        ],
        'label description PRODUCT_QTY'           => [
            'input'  => 'PRODUCT_QTY: [PRODUCT_QTY]',
            'output' => 'PRODUCT_QTY: 5',
        ],
        'multiple label description placeholders' => [
            'input'  => '[CUSTOMER_NOTE] | [ORDER_ID] | [PRODUCT_ID] | [PRODUCT_NAME] | [PRODUCT_SKU] | [PRODUCT_EAN] | [PRODUCT_QTY]',
            'output' => 'Hello | 123 | 456, 789 | Apple, Banana | A...',
        ],
    ]);

it('gets label description from settings', function (?string $setting, $labelDescription, $output) {
    $reset = mockPdkProperty('orderCalculators', [LabelDescriptionCalculator::class]);

    factory(LabelSettings::class)
        ->withDescription($setting)
        ->store();

    $order = createOrder($labelDescription);

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->labelDescription)->toEqual($output);

    $reset();
})
    ->with([
        'static setting, order value set to inherit'                 => [
            'setting' => 'value',
            'input'   => TriStateService::INHERIT,
            'output'  => 'value',
        ],
        'static setting, string set in order'                        => [
            'setting' => 'value',
            'input'   => 'hello',
            'output'  => 'hello',
        ],
        'static setting, empty string set in order'                  => [
            'setting' => 'value',
            'input'   => '',
            'output'  => '',
        ],
        'setting containing placeholder, order value set to inherit' => [
            'setting' => '[ORDER_ID]',
            'input'   => TriStateService::INHERIT,
            'output'  => '123',
        ],
    ]);
