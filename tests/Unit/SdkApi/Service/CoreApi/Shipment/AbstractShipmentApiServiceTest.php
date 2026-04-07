<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

// Concrete implementation for testing
class ConcreteShipmentApiServiceForTest extends AbstractShipmentApiService
{
    public function getShipmentApiInstance(): ShipmentApi
    {
        return $this->shipmentApi;
    }
}

// Tests for constructor
it('instantiates ShipmentApi', function () {
    $service     = new ConcreteShipmentApiServiceForTest();
    $shipmentApi = $service->getShipmentApiInstance();

    expect($shipmentApi)->toBeInstanceOf(ShipmentApi::class);
});

it('instantiated ShipmentApi uses configuration from parent', function () {
    TestBootstrapper::hasApiKey('configured-key');

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_ACCEPTANCE)
        ->store();

    $service     = new ConcreteShipmentApiServiceForTest();
    $shipmentApi = $service->getShipmentApiInstance();

    // Verify it was constructed (can't directly introspect private Guzzle config)
    expect($shipmentApi)->toBeInstanceOf(ShipmentApi::class);
    expect($shipmentApi->getConfig())->toBeInstanceOf(\MyParcelNL\Sdk\Client\Generated\CoreApi\Configuration::class);
    expect($shipmentApi->getConfig()->getHost())->toBe(Config::API_URL_ACCEPTANCE);
});
