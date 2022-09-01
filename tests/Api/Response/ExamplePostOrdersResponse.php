<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExamplePostOrdersResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'ids' => [
                    ['uuid' => '123'],
                    ['uuid' => '456'],
                ],
            ],
        ];
    }
}
