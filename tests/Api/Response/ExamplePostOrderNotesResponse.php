<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExamplePostOrderNotesResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'uuid'    => 'uuid-1',
                'author'  => 'customer',
                'note'    => 'This is a note',
                'created' => '2023-01-31 12:00:00',
                'updated' => '2023-01-31 12:00:01',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'order_notes';
    }
}
