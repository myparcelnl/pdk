<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetCarrierConfigurationResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'data' => [
                'carrier_configurations' => [
                    [
                        'carrier'                => 5,
                        'default_drop_off_point' => [
                            'name'          => 'broccoli',
                            'city'          => '',
                            'location_code' => '',
                            'location_name' => '',
                            'number'        => '',
                            'postal_code'   => '',
                            'street'        => '',
                        ],
                    ],
                ],
            ],
        ];
    }
}
