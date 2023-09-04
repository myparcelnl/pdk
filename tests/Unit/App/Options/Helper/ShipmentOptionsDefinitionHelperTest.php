<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('settings', 'tri-state');

usesShared(new UsesMockPdkInstance());

it('gets value from order', function (OrderOptionDefinitionInterface $definition) {
    $order = factory(PdkOrder::class)->make();

    $helper = new ShipmentOptionsDefinitionHelper($order);

    expect($helper->get($definition))->toEqual(TriStateService::INHERIT);
})->with('all shipment options');

it('gets value from order with all options enabled', function (OrderOptionDefinitionInterface $definition) {
    $order = factory(PdkOrder::class)
        ->withDeliveryOptionsWithAllOptions()
        ->make();

    $helper = new ShipmentOptionsDefinitionHelper($order);

    expect($helper->get($definition))->toEqual(TriStateService::ENABLED);
})->with('all shipment options');

