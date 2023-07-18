<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

dataset('pdkOrderNotesToFulfilmentNotes', [
    'empty' => [
        'fulfilmentOrderNote' => [],
        'pdkOrderNote'        => [],
    ],

    'with api identifier' => [
        'fulfilmentOrderNote' => [
            'uuid'   => '123',
            'author' => OrderNote::AUTHOR_CUSTOMER,
            'note'   => 'test',
        ],
        'pdkOrderNote'        => [
            'apiIdentifier' => '123',
            'author'        => OrderNote::AUTHOR_CUSTOMER,
            'note'          => 'test',
        ],
    ],

    'with all properties' => [
        'fulfilmentOrderNote' => [
            'uuid'      => '123',
            'author'    => OrderNote::AUTHOR_WEBSHOP,
            'note'      => 'creme brulee',
            'createdAt' => '2020-01-01 00:00:00',
            'updatedAt' => '2020-01-01 00:00:00',
        ],
        'pdkOrderNote'        => [
            'apiIdentifier' => '123',
            'author'        => OrderNote::AUTHOR_WEBSHOP,
            'note'          => 'creme brulee',
            'createdAt'     => '2020-01-01 00:00:00',
            'updatedAt'     => '2020-01-01 00:00:00',
        ],
    ],
]);
