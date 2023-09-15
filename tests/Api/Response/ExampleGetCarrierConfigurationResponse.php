<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Carrier\Model\Carrier;

class ExampleGetCarrierConfigurationResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'carrier'                => Carrier::CARRIER_POSTNL_ID,
                'default_cutoff_time'    => '17:00:00',
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
        ];
    }

    protected function getResponseProperty(): string
    {
        return 'carrier_configurations';
    }
}
