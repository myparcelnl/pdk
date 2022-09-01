<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorNotFoundResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorUnprocessableEntityResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('handles various error responses', function (string $response) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new $response());

    /** @var \MyParcelNL\Pdk\Account\Repository\ShopRepository $repository */
    $repository = $pdk->get(ShopRepository::class);

    expect(function () use ($repository) {
        $repository->getShop();
    })->toThrow(ApiException::class);
})->with([
    ExampleErrorUnprocessableEntityResponse::class,
    ExampleErrorNotFoundResponse::class,
    ExampleErrorResponse::class,
]);

it('handles a request with a query string', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $shipments = $repository->getByReferenceIdentifiers(['my_ref_id']);

    expect($shipments)
        ->toBeInstanceOf(Collection::class)
        ->and($shipments->first())
        ->toBeInstanceOf(Shipment::class);
});
