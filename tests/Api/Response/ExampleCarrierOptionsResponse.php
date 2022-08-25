<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleCarrierOptionsResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'data' => [
                'carrier_options' => [
                    [
                        'id'       => 7,
                        'carrier'  => ['id' => 5],
                        'enabled'  => true,
                        'optional' => true,
                    ],
                ],
            ],
        ];
    }
}
