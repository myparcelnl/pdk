<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorNotFoundResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorUnprocessableEntityResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('handles various error responses', function (string $response) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new $response());

    /** @var \MyParcelNL\Pdk\Account\Repository\ShopRepository $repository */
    $repository = Pdk::get(ShopRepository::class);

    expect(function () use ($repository) {
        $repository->getShop();
    })->toThrow(ApiException::class);
})->with([
    ExampleErrorUnprocessableEntityResponse::class,
    ExampleErrorNotFoundResponse::class,
    ExampleErrorResponse::class,
]);

it('handles a request with a query string', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $shipments = $repository->getByReferenceIdentifiers(['my_ref_id']);

    expect($shipments)
        ->toBeInstanceOf(Collection::class)
        ->and($shipments->first())
        ->toBeInstanceOf(Shipment::class);
});
