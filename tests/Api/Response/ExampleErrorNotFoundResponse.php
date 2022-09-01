<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use Symfony\Component\HttpFoundation\Response;

class ExampleErrorNotFoundResponse extends ExampleJsonResponse
{
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
