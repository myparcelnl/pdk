<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\Response;

class ExampleErrorUnprocessableEntityResponse extends GuzzleResponse
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
