<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

final class ExampleGetCarrierOptionsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'         => 1462,
                'carrier_id' => 3,
                'carrier'    => [
                    'id'   => 3,
                    'name' => 'cheapcargo',
                ],
                'enabled'    => 1,
                'optional'   => 0,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 7224,
                'carrier_id' => 7,
                'carrier'    => [
                    'id'   => 7,
                    'name' => 'bol.com',
                ],
                'enabled'    => 1,
                'optional'   => 0,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 7393,
                'carrier_id' => 12,
                'carrier'    => [
                    'id'   => 12,
                    'name' => 'upsstandard',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 7394,
                'carrier_id' => 13,
                'carrier'    => [
                    'id'   => 13,
                    'name' => 'upsexpresssaver',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 8382,
                'carrier_id' => 9,
                'carrier'    => [
                    'id'   => 9,
                    'name' => 'dhlforyou',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 8940,
                'label'      => 'absent_on_delivery_note_platform_1',
                'carrier_id' => 1,
                'carrier'    => [
                    'id'   => 1,
                    'name' => 'postnl',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 8942,
                'carrier_id' => 10,
                'carrier'    => [
                    'id'   => 10,
                    'name' => 'dhlparcelconnect',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'         => 9029,
                'carrier_id' => 11,
                'carrier'    => [
                    'id'   => 11,
                    'name' => 'dhleuroplus',
                ],
                'enabled'    => 1,
                'optional'   => 1,
                'primary'    => 1,
                'type'       => 'main',
            ],
            [
                'id'          => 12424,
                'carrier_id'  => 9,
                'carrier'     => [
                    'id'   => 9,
                    'name' => 'dhlforyou',
                ],
                'enabled'     => 1,
                'optional'    => 1,
                'primary'     => 0,
                'type'        => 'custom',
                'contract_id' => 677,
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
