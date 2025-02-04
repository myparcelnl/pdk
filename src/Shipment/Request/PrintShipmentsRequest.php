<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

class PrintShipmentsRequest extends PostShipmentsRequest
{
    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        $printerGroupId = Settings::get(LabelSettings::PRINTER_GROUP_ID, LabelSettings::ID);

        if (! $printerGroupId) {
            return parent::getHeaders();
        }

        return array_replace(
            parent::getHeaders(),
            [
                'Accept' => "application/vnd.shipment+json+print;printer-group-id=$printerGroupId;charset=utf-8;version=1.1",
            ]
        );
    }
}
