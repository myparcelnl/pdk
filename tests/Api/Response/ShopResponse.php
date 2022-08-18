<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class ShopResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode(['data' => ['shops' => [['id' => 3, 'name' => 'creme fraiche']]],])
        );
    }
}
