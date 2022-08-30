<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;

class PostShipmentsResponse extends JsonResponse
{
    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode([
                'data' => [
                    'ids' => [
                        [
                            'id'                   => 123,
                            'reference_identifier' => 'my_ref_id',
                        ],
                    ],
                ],
            ])
        );
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_CREATED;
    }
}
