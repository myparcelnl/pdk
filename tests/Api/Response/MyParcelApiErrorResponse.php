<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use MyParcelNL\Pdk\Base\HttpResponses;
use Psr\Http\Message\StreamInterface;

class MyParcelApiErrorResponse extends Response
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'errors' => [
                    [
                        'field'   => 'bloemkool',
                        'message' => 'verrot',
                    ],
                ],
            ])
        );
    }

    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    public function getStatusCode(): int
    {
        return HttpResponses::HTTP_UNPROCESSABLE_ENTITY;
    }
}
