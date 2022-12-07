<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class Example204NoContentResponse extends \GuzzleHttp\Psr7\Response
{
    public function getStatusCode(): int
    {
        return Response::HTTP_NO_CONTENT;
    }
}
