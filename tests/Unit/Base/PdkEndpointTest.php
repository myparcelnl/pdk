<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\App\Action\Backend\Account\DeleteAccountAction;
use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\ExportOrderAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\FetchOrdersAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\PostOrderNotesAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\PrintOrdersAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\SynchronizeOrdersAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\UpdateOrderAction;
use MyParcelNL\Pdk\App\Action\Backend\Settings\UpdatePluginSettingsAction;
use MyParcelNL\Pdk\App\Action\Backend\Settings\UpdateProductSettingsAction;
use MyParcelNL\Pdk\App\Action\Backend\Shipment\DeleteShipmentsAction;
use MyParcelNL\Pdk\App\Action\Backend\Shipment\ExportReturnAction;
use MyParcelNL\Pdk\App\Action\Backend\Shipment\PrintShipmentsAction;
use MyParcelNL\Pdk\App\Action\Backend\Shipment\UpdateShipmentsAction;
use MyParcelNL\Pdk\App\Action\Backend\Webhook\CreateWebhooksAction;
use MyParcelNL\Pdk\App\Action\Backend\Webhook\DeleteWebhooksAction;
use MyParcelNL\Pdk\App\Action\Backend\Webhook\FetchWebhooksAction;
use MyParcelNL\Pdk\App\Action\Shared\Context\FetchContextAction;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Frontend\PdkFrontendActions;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAction;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApiExceptionAction;
use MyParcelNL\Pdk\Tests\Bootstrap\MockExceptionAction;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function DI\get;
use function DI\value;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('endpoints');

usesShared(
    new UsesEachMockPdkInstance([
        CreateWebhooksAction::class        => get(MockAction::class),
        DeleteAccountAction::class         => get(MockAction::class),
        DeleteShipmentsAction::class       => get(MockAction::class),
        DeleteWebhooksAction::class        => get(MockAction::class),
        ExportOrderAction::class           => get(MockAction::class),
        ExportReturnAction::class          => get(MockAction::class),
        FetchContextAction::class          => get(MockAction::class),
        FetchOrdersAction::class           => get(MockAction::class),
        FetchWebhooksAction::class         => get(MockAction::class),
        PostOrderNotesAction::class        => get(MockAction::class),
        PrintOrdersAction::class           => get(MockAction::class),
        PrintShipmentsAction::class        => get(MockAction::class),
        SynchronizeOrdersAction::class     => get(MockAction::class),
        UpdateAccountAction::class         => get(MockAction::class),
        UpdateOrderAction::class           => get(MockAction::class),
        UpdatePluginSettingsAction::class  => get(MockAction::class),
        UpdateProductSettingsAction::class => get(MockAction::class),
        UpdateShipmentsAction::class       => get(MockAction::class),
    ])
);

dataset('backend actions', function () {
    return [
        PdkBackendActions::CREATE_WEBHOOKS,
        PdkBackendActions::DELETE_ACCOUNT,
        PdkBackendActions::DELETE_SHIPMENTS,
        PdkBackendActions::DELETE_WEBHOOKS,
        PdkBackendActions::EXPORT_ORDERS,
        PdkBackendActions::EXPORT_RETURN,
        PdkBackendActions::FETCH_ORDERS,
        PdkBackendActions::FETCH_WEBHOOKS,
        PdkBackendActions::POST_ORDER_NOTES,
        PdkBackendActions::PRINT_ORDERS,
        PdkBackendActions::PRINT_SHIPMENTS,
        PdkBackendActions::SYNCHRONIZE_ORDERS,
        PdkBackendActions::UPDATE_ACCOUNT,
        PdkBackendActions::UPDATE_ORDERS,
        PdkBackendActions::UPDATE_PLUGIN_SETTINGS,
        PdkBackendActions::UPDATE_PRODUCT_SETTINGS,
        PdkBackendActions::UPDATE_SHIPMENTS,
        PdkSharedActions::FETCH_CONTEXT,
    ];
});

dataset('frontend actions', function () {
    return [
        PdkFrontendActions::FETCH_CHECKOUT_CONTEXT,
        PdkSharedActions::FETCH_CONTEXT,
    ];
});

function testEndpoint(string $action, string $context): void
{
    /** @var \MyParcelNL\Pdk\App\Api\PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call($action, $context);

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_OK)
        ->and(json_decode($response->getContent(), true))
        ->toBe(['data' => ['success' => true]]);
}

it('calls pdk backend endpoints', function (string $action) {
    testEndpoint($action, PdkEndpoint::CONTEXT_BACKEND);
})->with('backend actions');

it('calls pdk frontend endpoints', function (string $action) {
    testEndpoint($action, PdkEndpoint::CONTEXT_FRONTEND);
})->with('frontend actions');

it('returns and logs error response when error is thrown', function () {
    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);

    $response = $endpoint->call('nonexistent', PdkEndpoint::CONTEXT_BACKEND);

    $responseContent = json_decode($response->getContent(), true);
    $logs            = $logger->getLogs();

    // Check if the file and line are set.
    expect(Arr::get($responseContent, 'errors.0.file'))
        ->toMatch('/\.php$/')
        ->and(Arr::get($responseContent, 'errors.0.line'))
        ->toBeInt();

    // Remove trace properties before comparing as they are not static.
    Arr::forget($responseContent, 'errors.0.trace');
    Arr::forget($logs, '0.context.response.errors.0.trace');

    $responseContext = [
        'message' => 'Action "nonexistent" does not exist.',
        'errors'  => [
            // Add the expected error response to the response context so that we don't have to manually compare the
            // file and line as they can change.
            array_replace(Arr::get($responseContent, 'errors.0'), [
                'message' => 'Action "nonexistent" does not exist.',
                'code'    => 0,
            ]),
        ],
    ];

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($responseContent)
        ->toBe($responseContext)
        ->and($logs)
        ->toBe([
            [
                'level'   => 'error',
                'message' => '[PDK]: An exception was thrown while executing an action',
                'context' => [
                    'action'   => 'nonexistent',
                    'context'  => PdkEndpoint::CONTEXT_BACKEND,
                    'response' => $responseContext,
                ],
            ],
        ]);
});

it('hides stack trace in frontend context', function () {
    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call('nonexistent', PdkEndpoint::CONTEXT_FRONTEND);

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and(json_decode($response->getContent(), true)['errors'][0]['trace'])
        ->toBe('Enable development mode to see stack trace.');
});

it('shows stack trace in frontend context in development mode', function () {
    PdkFactory::create(MockPdkConfig::create(['mode' => value(\MyParcelNL\Pdk\Base\Pdk::MODE_DEVELOPMENT)]));

    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);

    $response = $endpoint->call('nonexistent', PdkEndpoint::CONTEXT_FRONTEND);

    $trace = Arr::get(json_decode($response->getContent(), true), 'errors.0.trace');

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($trace)
        ->toBeArray()
        ->and($trace)
        ->not->toBeEmpty()
        ->and($trace[0])
        ->toHaveKeys(['file', 'line', 'function', 'class']);
});

it('returns error response when using the wrong context', function (string $action) {
    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call($action, PdkEndpoint::CONTEXT_FRONTEND);

    if (PdkSharedActions::FETCH_CONTEXT === $action) {
        expect($response->getStatusCode())->toBe(Response::HTTP_OK);
        return;
    }

    $responseContent = json_decode($response->getContent(), true);
    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($responseContent['message'])
        ->toBe("Action \"$action\" does not exist.");
})->with('backend actions');

it('returns error response on api exception', function () {
    mockPdkProperties([FetchOrdersAction::class => get(MockApiExceptionAction::class)]);

    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call(PdkBackendActions::FETCH_ORDERS, PdkEndpoint::CONTEXT_BACKEND);

    $responseContent = json_decode($response->getContent(), true);

    expect($response)
        ->getStatusCode()
        ->toBe(Response::HTTP_BAD_REQUEST)
        ->and($responseContent)
        ->toBe([
            'message'    => 'Request failed. Status code: 400. Message: boom',
            'request_id' => '12345',
            'errors'     => [
                [
                    'code'    => 24920,
                    'message' => 'Something went wrong',
                ],
                [
                    'code'    => 74892,
                    'message' => 'Something else also went wrong',
                ],
            ],
        ]);
});

it('returns error response on unknown exception', function () {
    mockPdkProperties([FetchOrdersAction::class => get(MockExceptionAction::class)]);

    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call(PdkBackendActions::FETCH_ORDERS, PdkEndpoint::CONTEXT_BACKEND);

    $responseContent = json_decode($response->getContent(), true);

    // Check if the file and line are set.
    expect(Arr::get($responseContent, 'errors.0.file'))
        ->toMatch('/\.php$/')
        ->and(Arr::get($responseContent, 'errors.0.line'))
        ->toBeInt()
        ->and(Arr::get($responseContent, 'errors.0.trace'))
        ->not->toBeEmpty();

    // Remove trace properties before comparing as they are not static.
    Arr::forget($responseContent, 'errors.0.trace');
    Arr::forget($responseContent, 'errors.0.file');
    Arr::forget($responseContent, 'errors.0.line');
    Arr::forget($responseContent, 'errors.1.trace');
    Arr::forget($responseContent, 'errors.1.file');
    Arr::forget($responseContent, 'errors.1.line');

    expect($response)
        ->getStatusCode()
        ->toBe(Response::HTTP_BAD_REQUEST)
        ->and($responseContent)
        ->toBe([
            'message' => 'Something went terribly wrong',
            'errors'  => [
                [
                    'code'    => 5,
                    'message' => 'Something went terribly wrong',
                ],
                [
                    'code'    => 1,
                    'message' => 'Previous exception',
                ],
            ],
        ]);
});
