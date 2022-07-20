<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\MyParcelApiErrorResponse;
use MyParcelNL\Pdk\Tests\Api\Response\NotFoundResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\UnprocessableEntityResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

it('handles various error responses', function (string $response) {
    $pdk = PdkFactory::create(MockConfig::DEFAULT_CONFIG);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(new $response());

    /** @var \MyParcelNL\Pdk\Account\Repository\ShopRepository $repository */
    $repository = $pdk->get(ShopRepository::class);

    expect(function () use ($repository) {
        $repository->getShop();
    })->toThrow(ApiException::class);
})->with([
    UnprocessableEntityResponse::class,
    NotFoundResponse::class,
    MyParcelApiErrorResponse::class,
]);

it('handles a request with a query string', function () {
    $pdk = PdkFactory::create(MockConfig::DEFAULT_CONFIG);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(new ShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $shipments = $repository->getShipments('my_ref_id');
    expect($shipments)
        ->toBeInstanceOf(Collection::class)
        ->and($shipments->first())
        ->toBeInstanceOf(AbstractConsignment::class);
});
