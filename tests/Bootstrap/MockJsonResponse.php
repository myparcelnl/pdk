<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Psr7\Utils;
use MyParcelNL\Pdk\Tests\Api\Response\JsonResponse;
use Psr\Http\Message\StreamInterface;

class MockJsonResponse extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode(['data' => ['shops' => [['id' => 3, 'name' => 'broccoli']]],])
        );
    }
}
