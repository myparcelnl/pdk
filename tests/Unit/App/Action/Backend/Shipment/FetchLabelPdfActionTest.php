<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleErrorResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetLabelPdfNotReadyResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsPdfResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesApiMock());

it('returns the pdf when it is ready', function () {
    MockApi::enqueue(new ExampleGetShipmentLabelsPdfResponse());

    $response = Actions::execute(PdkBackendActions::FETCH_LABEL_PDF, ['labelId' => 'e0d7308e3f32']);
    $content  = json_decode($response->getContent(), true);
    $request  = MockApi::ensureLastRequest();

    expect($request->getUri()->getPath())
        ->toBe('API/pdfs/e0d7308e3f32')
        ->and($request->getHeaderLine('Accept'))
        ->toBe('application/pdf')
        ->and(base64_decode($content['data']['pdfs']['data']))
        ->toStartWith('%PDF-1.6');
});

it('reports the pdf as pending while the api is still generating it', function () {
    MockApi::enqueue(new ExampleGetLabelPdfNotReadyResponse());

    $response = Actions::execute(PdkBackendActions::FETCH_LABEL_PDF, ['labelId' => 'e0d7308e3f32']);
    $content  = json_decode($response->getContent(), true);

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($content['data']['pdfs'])
        ->toBe(['pending' => true]);
});

it('fails on other api errors', function () {
    MockApi::enqueue(new ExampleErrorResponse());

    Actions::execute(PdkBackendActions::FETCH_LABEL_PDF, ['labelId' => 'e0d7308e3f32']);
})->throws(\MyParcelNL\Pdk\Api\Exception\ApiException::class);

it('rejects a label id that is not a plain hash', function (string $labelId) {
    Actions::execute(PdkBackendActions::FETCH_LABEL_PDF, ['labelId' => $labelId]);
})
    ->throws(\InvalidArgumentException::class)
    ->with([
        'empty'          => [''],
        'path traversal' => ['../shipments'],
        'full url'       => ['https://example.com/pdfs/abc'],
    ]);
