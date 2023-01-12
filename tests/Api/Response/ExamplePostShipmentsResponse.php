<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExamplePostShipmentsResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'shipments' => [
                    ['id' => '123'],
                    ['id' => '456'],
                ],
            ],
        ];
    }
}
