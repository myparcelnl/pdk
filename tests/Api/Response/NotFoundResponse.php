<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Base\Http\ResponseCodes;

class NotFoundResponse extends Response
{
    public function getStatusCode(): int
    {
        return ResponseCodes::HTTP_NOT_FOUND;
    }
}
