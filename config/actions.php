<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Action\Backend\Account\DeleteAccountAction;
use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateSubscriptionFeaturesAction;
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
use MyParcelNL\Pdk\App\Action\Frontend\Context\FetchCheckoutContextAction;
use MyParcelNL\Pdk\App\Action\Shared\Context\FetchContextAction;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Frontend\PdkFrontendActions;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Api\Shared\PdkSharedActions;
use MyParcelNL\Pdk\App\Request\Account\DeleteAccountEndpointRequest;
use MyParcelNL\Pdk\App\Request\Account\UpdateAccountEndpointRequest;
use MyParcelNL\Pdk\App\Request\Account\UpdateSubscriptionFeaturesEndpointRequest;
use MyParcelNL\Pdk\App\Request\Context\FetchContextEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\ExportOrdersEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\FetchOrdersEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\PostOrderNotesEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\PrintOrdersEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\SynchronizeOrdersEndpointRequest;
use MyParcelNL\Pdk\App\Request\Orders\UpdateOrdersEndpointRequest;
use MyParcelNL\Pdk\App\Request\Settings\UpdatePluginSettingsEndpointRequest;
use MyParcelNL\Pdk\App\Request\Settings\UpdateProductSettingsEndpointRequest;
use MyParcelNL\Pdk\App\Request\Shipment\DeleteShipmentsEndpointRequest;
use MyParcelNL\Pdk\App\Request\Shipment\ExportReturnEndpointRequest;
use MyParcelNL\Pdk\App\Request\Shipment\PrintShipmentsEndpointRequest;
use MyParcelNL\Pdk\App\Request\Shipment\UpdateShipmentsEndpointRequest;
use MyParcelNL\Pdk\App\Request\Webhook\CreateWebhooksEndpointRequest;
use MyParcelNL\Pdk\App\Request\Webhook\DeleteWebhooksEndpointRequest;
use MyParcelNL\Pdk\App\Request\Webhook\FetchWebhooksEndpointRequest;

return [
    PdkEndpoint::CONTEXT_SHARED => [
        /**
         * Fetch context
         */
        PdkSharedActions::FETCH_CONTEXT => [
            'request' => FetchContextEndpointRequest::class,
            'action'  => FetchContextAction::class,
        ],
    ],

    PdkEndpoint::CONTEXT_FRONTEND => [
        /**
         * Get checkout context
         */
        PdkFrontendActions::FETCH_CHECKOUT_CONTEXT => [
            'request' => FetchContextEndpointRequest::class,
            'action'  => FetchCheckoutContextAction::class,
        ],
    ],

    PdkEndpoint::CONTEXT_BACKEND => [
        /**
         * Delete account.
         */
        PdkBackendActions::DELETE_ACCOUNT               => [
            'request' => DeleteAccountEndpointRequest::class,
            'action'  => DeleteAccountAction::class,
        ],

        /**
         * Update account.
         */
        PdkBackendActions::UPDATE_ACCOUNT               => [
            'request' => UpdateAccountEndpointRequest::class,
            'action'  => UpdateAccountAction::class,
        ],

        /**
         * Fetch subscription features from the API.
         */
        PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES => [
            'request' => UpdateSubscriptionFeaturesEndpointRequest::class,
            'action'  => UpdateSubscriptionFeaturesAction::class,
        ],

        /**
         * Synchronize orders with the API.
         */
        PdkBackendActions::SYNCHRONIZE_ORDERS           => [
            'request' => SynchronizeOrdersEndpointRequest::class,
            'action'  => SynchronizeOrdersAction::class,
        ],

        /**
         * Exports an order to MyParcel as order or shipment, depending on "mode" setting.
         */
        PdkBackendActions::EXPORT_ORDERS                => [
            'request' => ExportOrdersEndpointRequest::class,
            'action'  => ExportOrderAction::class,
        ],

        PdkBackendActions::POST_ORDER_NOTES        => [
            'request' => PostOrderNotesEndpointRequest::class,
            'action'  => PostOrderNotesAction::class,
        ],

        /**
         * Retrieve orders from the plugin.
         */
        PdkBackendActions::FETCH_ORDERS            => [
            'request' => FetchOrdersEndpointRequest::class,
            'action'  => FetchOrdersAction::class,
        ],

        /**
         * Prints the order.
         */
        PdkBackendActions::PRINT_ORDERS            => [
            'request' => PrintOrdersEndpointRequest::class,
            'action'  => PrintOrdersAction::class,
        ],

        /**
         * Update the order in the plugin.
         */
        PdkBackendActions::UPDATE_ORDERS           => [
            'request' => UpdateOrdersEndpointRequest::class,
            'action'  => UpdateOrderAction::class,
        ],

        /**
         * Get new shipments data from the API.
         */
        PdkBackendActions::UPDATE_SHIPMENTS        => [
            'request' => UpdateShipmentsEndpointRequest::class,
            'action'  => UpdateShipmentsAction::class,
        ],

        /**
         * Soft delete shipments in the plugin.
         */
        PdkBackendActions::DELETE_SHIPMENTS        => [
            'request' => DeleteShipmentsEndpointRequest::class,
            'action'  => DeleteShipmentsAction::class,
        ],

        /**
         * Print shipment labels
         */
        PdkBackendActions::PRINT_SHIPMENTS         => [
            'request' => PrintShipmentsEndpointRequest::class,
            'action'  => PrintShipmentsAction::class,
        ],

        /**
         * Update plugin settings
         */
        PdkBackendActions::UPDATE_PLUGIN_SETTINGS  => [
            'request' => UpdatePluginSettingsEndpointRequest::class,
            'action'  => UpdatePluginSettingsAction::class,
        ],

        /**
         * Update product settings
         */
        PdkBackendActions::UPDATE_PRODUCT_SETTINGS => [
            'request' => UpdateProductSettingsEndpointRequest::class,
            'action'  => UpdateProductSettingsAction::class,
        ],

        /**
         * Create return shipment
         */
        PdkBackendActions::EXPORT_RETURN           => [
            'request' => ExportReturnEndpointRequest::class,
            'action'  => ExportReturnAction::class,
        ],

        /**
         * Create webhooks
         */
        PdkBackendActions::CREATE_WEBHOOKS         => [
            'request' => CreateWebhooksEndpointRequest::class,
            'action'  => CreateWebhooksAction::class,
        ],

        /**
         * Delete webhooks
         */
        PdkBackendActions::DELETE_WEBHOOKS         => [
            'request' => DeleteWebhooksEndpointRequest::class,
            'action'  => DeleteWebhooksAction::class,
        ],

        /**
         * Fetch webhooks
         */
        PdkBackendActions::FETCH_WEBHOOKS          => [
            'request' => FetchWebhooksEndpointRequest::class,
            'action'  => FetchWebhooksAction::class,
        ],
    ],
];
