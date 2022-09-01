<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShipmentLabelsLinkResponse extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'data' => [
                'pdfs' => [
                    'url' => '/pdfs/label_hash',
                ],
            ],
        ];
    }
}
