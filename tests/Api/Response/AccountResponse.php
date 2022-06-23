<?php

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class AccountResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::StreamFor(
            json_encode([
                'data' => [
                    'accounts' => [
                        [
                            'platform_id' => 3,
                            'id'          => 3,
                            'shops'       => [['id' => 3, 'name' => 'bloemkool']],
                        ],
                    ],
                ],
            ])
        );
    }
}
