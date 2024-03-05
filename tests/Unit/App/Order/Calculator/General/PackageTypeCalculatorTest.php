<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function MyParcelNL\Pdk\Tests\factory;

it('calculates package type', function (string $platform, array $options, string $result) {
    MockPdkFactory::create(['platform' => $platform, 'orderCalculators' => [PackageTypeCalculator::class]]);

    $fakeCarrier = factory(Carrier::class)
        ->withCapabilities(factory(CarrierCapabilities::class)->withAllOptions());

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc($options['cc'] ?? Platform::get('localCountry')))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrier)
                ->withPackageType($options['packageType'])
                ->withAllShipmentOptions()
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe($result);
})
    ->with('platforms')
    ->with([
        'local country, package type package' => [
            'options' => ['packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'local country, package type letter' => [
            'options' => ['packageType' => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,],
            'result'  => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        ],

        'non-local country, package type package' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'non-local country, package type letter' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        ],

        'non-local country, package type mailbox' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'non-local country, package type package small' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        ],
    ]);
