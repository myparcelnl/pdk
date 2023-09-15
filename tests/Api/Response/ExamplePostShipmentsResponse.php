<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExamplePostShipmentsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            ['id' => '123'],
            ['id' => '456'],
            ['id' => '789'],
        ];
    }

    protected function getResponseProperty(): string
    {
        return 'ids';
    }
}
