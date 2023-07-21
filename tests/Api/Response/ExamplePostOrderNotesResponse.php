<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExamplePostOrderNotesResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'order_notes' => [
                    [
                        'uuid'   => 'uuid-1',
                        'author' => 'customer',
                        'note'   => 'This is a note',
                    ],
                ],
            ],
        ];
    }
}
