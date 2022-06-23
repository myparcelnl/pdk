<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Base\HttpResponses;

class NotFoundResponse extends Response
{
    public function getStatusCode(): int
    {
        return HttpResponses::HTTP_NOT_FOUND;
    }
}
