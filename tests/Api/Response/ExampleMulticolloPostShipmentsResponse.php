<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleMulticolloPostShipmentsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            ['id' => '123'],
            ['id' => '456'],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'ids';
    }
}
