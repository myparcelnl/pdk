<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use Psr\Http\Message\StreamInterface;

class PostOrdersResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'data' => [
                    'orders' => [
                        ['uuid' => '123'],
                        ['uuid' => '456'],
                    ],
                ],
            ])
        );
    }
}
