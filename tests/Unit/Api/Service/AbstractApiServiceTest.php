<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorNotFoundResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorUnprocessableEntityResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Mocks\MockApiResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\src\Support\Arr;
use Psr\Log\LogLevel;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('handles various error responses', function (string $response) {
    MockApi::enqueue(new $response());

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
    MockApi::enqueue(new ExampleGetShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $shipments = $repository->getByReferenceIdentifiers(['my_ref_id']);

    expect($shipments)
        ->toBeInstanceOf(Collection::class)
        ->and($shipments->first())
        ->toBeInstanceOf(Shipment::class);
});

it('creates log context with obfuscated authorization header', function () {
    TestBootstrapper::hasApiKey();

    MockApi::enqueue(new ExampleGetShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);

    /** @var \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = Pdk::get(ApiServiceInterface::class);

    $request = new Request([
        'headers'    => [
            'Authorization' => 'bearer this-is-some-fake-value',
            'Content-Type'  => 'application/json',
        ],
        'uri'        => 'test',
        'method'     => 'POST',
        'parameters' => [],
        'body'       => json_encode(['test' => 'test']),
    ]);

    $apiService->doRequest($request, MockApiResponse::class);

    $lastLog = Arr::last($logger->getLogs());

    expect($lastLog['level'])
        ->toBe(LogLevel::DEBUG)
        ->and($lastLog['message'])
        ->toBe('[PDK]: Successfully sent request')
        ->and(array_keys($lastLog['context']))
        ->toEqual(['request', 'response'])
        ->and(array_keys($lastLog['context']['request']))
        ->toEqual(['uri', 'method', 'headers', 'body'])
        ->and(array_keys($lastLog['context']['response']))
        ->toEqual(['code', 'body'])
        // Expect header keys to be normalized and authorization header to be hidden
        ->and($lastLog['context']['request']['headers'])
        ->toBe([
            'authorization' => '***',
            'content-type'  => 'application/json',
        ]);
});
