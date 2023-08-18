<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Api\Response;

use MyParcelNL\Pdk\Carrier\Model\Carrier;

class ExampleGetCarrierOptionsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'       => 7,
                'carrier'  => ['id' => Carrier::CARRIER_POSTNL_ID],
                'enabled'  => true,
                'optional' => false,
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'carrier_options';
    }
}
