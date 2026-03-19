<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('calculates package type', function (
    string $cc,
    string $packageType,
    bool $allowInternationalMailbox,
    bool $hasCarrierSmallPackageContract,
    string $expectedPackageType
) {
    TestBootstrapper::forPlatform(Proposition::MYPARCEL_NAME);

    mockPdkProperties([
        'orderCalculators' => [PackageTypeCalculator::class],
    ]);

    $carrier = factory(Carrier::class)->withAllCapabilities();

    factory(CarrierSettings::class, $carrier->make()->carrier)
        ->withAllowInternationalMailbox($allowInternationalMailbox)
        ->store();

    factory(AccountGeneralSettings::class)
        ->withHasCarrierSmallPackageContract($hasCarrierSmallPackageContract)
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(
            factory(ShippingAddress::class)->withCc($cc)
        )
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType($packageType)
                ->withAllShipmentOptions()
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe($expectedPackageType);
})->with([
    'local country, package' => [
        'cc'                            => CountryCodes::CC_NL,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'local country, mailbox' => [
        'cc'                            => CountryCodes::CC_NL,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
    ],

    'local country, letter' => [
        'cc'                            => CountryCodes::CC_NL,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
    ],

    'local country, digital stamp' => [
        'cc'                            => CountryCodes::CC_NL,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
    ],

    'local country, package small' => [
        'cc'                            => CountryCodes::CC_NL,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
    ],

    'non-local country, package' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'non-local country, letter' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
    ],

    'non-local country, package small' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
    ],

    'non-local country, digital stamp falls back to package' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'non-local country, mailbox without international mailbox settings falls back to package' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'non-local country, mailbox with only carrier setting falls back to package' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'allowInternationalMailbox'     => true,
        'hasCarrierSmallPackageContract' => false,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'non-local country, mailbox with only account flag falls back to package' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'allowInternationalMailbox'     => false,
        'hasCarrierSmallPackageContract' => true,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
    ],

    'non-local country, mailbox with both settings enabled stays mailbox' => [
        'cc'                            => CountryCodes::CC_GB,
        'packageType'                   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        'allowInternationalMailbox'     => true,
        'hasCarrierSmallPackageContract' => true,
        'expectedPackageType'           => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
    ],
]);
