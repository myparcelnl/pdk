<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\ExportOrderAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\FetchOrdersAction;
use MyParcelNL\Pdk\App\Action\Backend\Order\PrintOrdersAction;
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
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAction;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function DI\value;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('endpoints');

$pdkConfig = [
    CreateWebhooksAction::class        => autowire(MockAction::class),
    DeleteShipmentsAction::class       => autowire(MockAction::class),
    DeleteWebhooksAction::class        => autowire(MockAction::class),
    ExportOrderAction::class           => autowire(MockAction::class),
    ExportReturnAction::class          => autowire(MockAction::class),
    FetchContextAction::class          => autowire(MockAction::class),
    FetchOrdersAction::class           => autowire(MockAction::class),
    UpdateShipmentsAction::class       => autowire(MockAction::class),
    FetchWebhooksAction::class         => autowire(MockAction::class),
    PrintOrdersAction::class           => autowire(MockAction::class),
    PrintShipmentsAction::class        => autowire(MockAction::class),
    UpdateAccountAction::class         => autowire(MockAction::class),
    UpdateOrderAction::class           => autowire(MockAction::class),
    UpdatePluginSettingsAction::class  => autowire(MockAction::class),
    UpdateProductSettingsAction::class => autowire(MockAction::class),
];
usesShared(
    new UsesEachMockPdkInstance($pdkConfig)
);

dataset('backendActions', function () {
    return [
        PdkBackendActions::UPDATE_ACCOUNT,
        PdkBackendActions::EXPORT_ORDERS,
        PdkBackendActions::FETCH_ORDERS,
        PdkBackendActions::PRINT_ORDERS,
        PdkBackendActions::UPDATE_ORDERS,
        PdkBackendActions::EXPORT_RETURN,
        PdkBackendActions::DELETE_SHIPMENTS,
        PdkBackendActions::PRINT_SHIPMENTS,
        PdkBackendActions::UPDATE_SHIPMENTS,
        PdkBackendActions::UPDATE_PLUGIN_SETTINGS,
        PdkBackendActions::UPDATE_PRODUCT_SETTINGS,
        PdkBackendActions::CREATE_WEBHOOKS,
        PdkBackendActions::DELETE_WEBHOOKS,
        PdkBackendActions::FETCH_WEBHOOKS,
        PdkSharedActions::FETCH_CONTEXT,
    ];
});

dataset('frontendActions', function () {
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
})
    ->with('backendActions');

it('calls pdk frontend endpoints', function (string $action) {
    testEndpoint($action, PdkEndpoint::CONTEXT_FRONTEND);
})
    ->with('frontendActions');

it('returns error response on nonexistent action', function () {
    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call('nonexistent', PdkEndpoint::CONTEXT_BACKEND);

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'message' => 'Action "nonexistent" does not exist.',
            'errors'  => [
                [
                    'status'  => 422,
                    'code'    => 0,
                    'message' => 'Action "nonexistent" does not exist.',
                    'trace'   => 'Enable development mode to see stack trace.',
                ],
            ],
        ]);
});

it('shows stack trace in development mode', function () {
    PdkFactory::create(MockPdkConfig::create(['mode' => value(\MyParcelNL\Pdk\Base\Pdk::MODE_DEVELOPMENT)]));

    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call('nonexistent', PdkEndpoint::CONTEXT_BACKEND);

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and(json_decode($response->getContent(), true)['errors'][0]['trace'])
        ->toBeArray();
});

it('throws exception when using the wrong context', function (string $action) {
    /** @var PdkEndpoint $endpoint */
    $endpoint = Pdk::get(PdkEndpoint::class);
    $response = $endpoint->call($action, PdkEndpoint::CONTEXT_FRONTEND);

    if (PdkSharedActions::FETCH_CONTEXT === $action) {
        expect($response->getStatusCode())->toBe(Response::HTTP_OK);
        return;
    }

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'message' => "Action \"$action\" does not exist.",
            'errors'  => [
                [
                    'status'  => 422,
                    'code'    => 0,
                    'message' => "Action \"$action\" does not exist.",
                    'trace'   => 'Enable development mode to see stack trace.',
                ],
            ],
        ]);
})->with('backendActions');
