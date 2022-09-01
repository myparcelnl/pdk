<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsPdfResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

$array              = array_fill(0, 30, 'appelboom');
$bulkShipmentsArray = array_map(function ($item, $index) {
    return ['id' => (int) $index + 1];
}, $array, array_keys($array));

dataset('collections', [
    'single shipment id'                => [
        'collection' => [
            ['id' => 5],
        ],
        'format'     => null,
        'position'   => null,
        'path'       => 'API/shipment_labels/5',
        'query'      => '',
    ],
    'multiple shipment ids'             => [
        'collection' => [
            ['id' => 5],
            ['id' => 6],
        ],
        'format'     => null,
        'position'   => null,
        'path'       => 'API/shipment_labels/5;6',
        'query'      => '',
    ],
    'with position'                     => [
        'collection' => [
            ['id' => 12425],
        ],
        'format'     => null,
        'position'   => [1],
        'path'       => 'API/shipment_labels/12425',
        'query'      => 'positions=1',
    ],
    'a4 format'                         => [
        'collection' => [
            ['id' => 5],
        ],
        'format'     => 'a4',
        'position'   => null,
        'path'       => 'API/shipment_labels/5',
        'query'      => 'format=a4',
    ],
    'a6 format'                         => [
        'collection' => [
            ['id' => 5],
        ],
        'format'     => 'a6',
        'position'   => null,
        'path'       => 'API/shipment_labels/5',
        'query'      => 'format=a6',
    ],
    'a6 format with positions'          => [
        'collection' => [
            ['id' => 5],
        ],
        'format'     => 'a6',
        'position'   => [2, 3],
        'path'       => 'API/shipment_labels/5',
        'query'      => 'format=a6&positions=2%3B3',
    ],
    'bulk with a6 format and positions' => [
        'collection' => $bulkShipmentsArray,
        'format'     => 'a6',
        'position'   => [2, 3],
        'path'       => 'API/v2/shipment_labels/1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20;21;22;23;24;25;26;27;28;29;30',
        'query'      => 'format=a6&positions=2%3B3',
    ],
]);

it(
    'downloads labels as link',
    function (array $collection, ?string $format, ?array $position, string $path, string $query) {
        $pdk = PdkFactory::create(MockPdkConfig::create());
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
        $api  = $pdk->get(ApiServiceInterface::class);
        $mock = $api->getMock();

        $mock->append(
            count($collection) > 25
                ? new ExampleGetShipmentLabelsLinkV2Response()
                : new ExampleGetShipmentLabelsLinkResponse()
        );

        /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
        $repository = $pdk->get(ShipmentRepository::class);

        $response = $repository->fetchLabelLink(new ShipmentCollection($collection), $format, $position);
        $request  = $mock->getLastRequest();

        if (! $request) {
            throw new RuntimeException('Request not found.');
        }

        $uri = $request->getUri();

        expect($uri->getQuery())
            ->toBe($query)
            ->and($uri->getPath())
            ->toBe($path)
            ->and($response)
            ->toBeInstanceOf(ShipmentCollection::class)
            ->and($response->label->link)
            ->toStartWith('API/pdfs/');
    }
)->with('collections');

it(
    'downloads labels as pdf',
    function (array $collection, ?string $format, ?array $position, string $path, string $query) {
        $pdk = PdkFactory::create(MockPdkConfig::create());
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
        $api  = $pdk->get(ApiServiceInterface::class);
        $mock = $api->getMock();
        $mock->append(new ExampleGetShipmentLabelsPdfResponse());

        /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
        $repository = $pdk->get(ShipmentRepository::class);

        $response = $repository->fetchLabelPdf(new ShipmentCollection($collection), $format, $position);
        $request  = $mock->getLastRequest();

        if (! $request) {
            throw new RuntimeException('Request not found.');
        }

        $uri = $request->getUri();

        expect($uri->getQuery())
            ->toBe($query)
            ->and($uri->getPath())
            ->toBe($path)
            ->and($response)
            ->toBeInstanceOf(ShipmentCollection::class)
            ->and($response->label->pdf)
            ->toStartWith('%PDF-1.6');
    }
)->with('collections');
