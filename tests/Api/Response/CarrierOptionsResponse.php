<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class CarrierOptionsResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::StreamFor(
            json_encode(
                [
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
                ]
            )
        );
    }
}
