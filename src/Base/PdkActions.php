<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

class PdkActions
{
    /**
     * Required actions
     */
    public const EXPORT_ORDER           = 'exportOrder';
    public const EXPORT_AND_PRINT_ORDER = 'exportAndPrintOrder';
    public const GET_ORDER_DATA         = 'getOrderData';
    public const UPDATE_TRACKING_NUMBER = 'updateTrackingNumber';
    public const REQUIRED               = [
        PdkActions::EXPORT_ORDER,
        PdkActions::EXPORT_AND_PRINT_ORDER,
        PdkActions::GET_ORDER_DATA,
    ];
    public const OPTIONAL               = [
        self::UPDATE_TRACKING_NUMBER,
    ];
}
