<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;

class MockApiResponse extends AbstractApiResponseWithBody
{
    protected function parseResponseBody(): void
    {
    }
}
