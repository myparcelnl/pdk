<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetAccountsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'platform_id' => 3,
                'id'          => 3,
                'shops'       => [
                    [
                        'id'   => 3,
                        'name' => 'bloemkool',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'accounts';
    }
}
