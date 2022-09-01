<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Request\Request;

class GetShipmentsRequest extends Request
{
    public function getPath(): string
    {
        return '/shipments';
    }
}
