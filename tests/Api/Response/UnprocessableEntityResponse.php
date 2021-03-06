<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use MyParcelNL\Pdk\Base\Http\ResponseCodes;
use Psr\Http\Message\StreamInterface;

class UnprocessableEntityResponse extends Response
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(json_encode([]));
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    public function getStatusCode(): int
    {
        return ResponseCodes::HTTP_UNPROCESSABLE_ENTITY;
    }
}
