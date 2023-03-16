<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Plugin\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Context\FetchContextAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\ExportOrderAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\FetchOrdersAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\PrintOrdersAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\UpdateOrderAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Settings\UpdatePluginSettingsAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Settings\UpdateProductSettingsAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Shipment\DeleteShipmentsAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Shipment\ExportReturnAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Shipment\PrintShipmentsAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Shipment\UpdateShipmentsAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Webhook\CreateWebhooksAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Webhook\DeleteWebhooksAction;
use MyParcelNL\Pdk\Plugin\Action\Backend\Webhook\FetchWebhooksAction;
use MyParcelNL\Pdk\Plugin\Action\Frontend\Context\FetchCheckoutContextAction;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Api\Frontend\PdkFrontendActions;
use MyParcelNL\Pdk\Plugin\Request\Account\UpdateAccountEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Context\FetchContextEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Orders\ExportOrdersEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Orders\FetchOrdersEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Orders\PrintOrdersEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Orders\UpdateOrdersEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Settings\UpdatePluginSettingsEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Settings\UpdateProductSettingsEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Shipment\DeleteShipmentsEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Shipment\ExportReturnEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Shipment\PrintShipmentsEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Shipment\UpdateShipmentsEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Webhook\CreateWebhooksEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Webhook\DeleteWebhooksEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\Webhook\FetchWebhooksEndpointRequest;

return [
    'shared' => [],

    'frontend' => [
        /**
         * Get checkout context
         */
        PdkFrontendActions::FETCH_CHECKOUT_CONTEXT => [
            'request' => FetchContextEndpointRequest::class,
            'action'  => FetchCheckoutContextAction::class,
        ],
    ],

    'backend' => [
        /**
         * Update account.
         */
        PdkBackendActions::UPDATE_ACCOUNT          => [
            'request' => UpdateAccountEndpointRequest::class,
            'action'  => UpdateAccountAction::class,
        ],

        /**
         * Exports an order to MyParcel as order or shipment, depending on "mode" setting.
         */
        PdkBackendActions::EXPORT_ORDERS           => [
            'request' => ExportOrdersEndpointRequest::class,
            'action'  => ExportOrderAction::class,
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
        PdkBackendActions::UPDATE_SHIPMENTS => [
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

        /**
         * Fetch context
         */
        PdkBackendActions::FETCH_CONTEXT           => [
            'request' => FetchContextEndpointRequest::class,
            'action'  => FetchContextAction::class,
        ],
    ],
];
