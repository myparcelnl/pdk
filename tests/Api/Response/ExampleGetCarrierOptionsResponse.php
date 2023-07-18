<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Carrier\Model\Carrier;

class ExampleGetCarrierOptionsResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'data' => [
                'carrier_options' => [
                    [
                        'id'       => 7,
                        'carrier'  => ['id' => Carrier::CARRIER_POSTNL_ID],
                        'enabled'  => true,
                        'optional' => false,
                    ],
                ],
            ],
        ];
    }
}
