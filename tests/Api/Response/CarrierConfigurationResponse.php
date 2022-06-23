<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class CarrierConfigurationResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
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
            ])
        );
    }
}
