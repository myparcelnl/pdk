<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Plugin\Action\Order\ExportOrderAction;
use MyParcelNL\Pdk\Plugin\Action\Order\ExportPrintOrderAction;
use MyParcelNL\Pdk\Plugin\Action\Order\GetOrderDataAction;
use MyParcelNL\Pdk\Plugin\Action\Order\PrintOrderAction;
use MyParcelNL\Pdk\Plugin\Request\ExportOrderEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\ExportPrintOrderEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\GetOrderDataEndpointRequest;
use MyParcelNL\Pdk\Plugin\Request\PrintOrderEndpointRequest;

return [
    'endpoints' => [
        PdkActions::EXPORT_ORDER => [
            'request' => ExportOrderEndpointRequest::class,
            'action'  => ExportOrderAction::class,
        ],

        PdkActions::EXPORT_AND_PRINT_ORDER => [
            'request' => ExportPrintOrderEndpointRequest::class,
            'action'  => ExportPrintOrderAction::class,
        ],

        PdkActions::PRINT_ORDER => [
            'request' => PrintOrderEndpointRequest::class,
            'action'  => PrintOrderAction::class,
        ],

        PdkActions::GET_ORDER_DATA => [
            'request' => GetOrderDataEndpointRequest::class,
            'action'  => GetOrderDataAction::class,
        ],
    ],

    'optional' => [],
];
