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
                        'note'   => 'This is a note',
                        'author' => 'customer',
                    ],
                ],
            ],
        ];
    }
}
