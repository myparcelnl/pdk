<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShopsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'   => 3,
                'name' => 'creme fraiche',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'shops';
    }
}
