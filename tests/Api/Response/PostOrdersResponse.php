<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class PostOrdersResponse extends Response
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'data' => [
                    'ids' => [
                        ['uuid' => '123'],
                        ['uuid' => '456'],
                    ],
                ],
            ])
        );
    }
}
