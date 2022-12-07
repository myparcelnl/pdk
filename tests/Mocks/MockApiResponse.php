<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class MockApiResponse extends ApiResponseWithBody
{
    protected function parseResponseBody(): void
    {
    }
}
