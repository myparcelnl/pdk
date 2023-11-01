<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Base\Http\ResponseCodes;

final class ExamplePostWebhooksResponse extends ExamplePostIdsResponse
{
    public function getStatusCode(): int
    {
        return ResponseCodes::HTTP_OK;
    }
}
