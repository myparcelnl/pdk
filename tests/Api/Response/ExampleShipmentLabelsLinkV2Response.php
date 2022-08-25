<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleShipmentLabelsLinkV2Response extends ExampleJsonResponse
{
    public function getContent(): array
    {
        return [
            'data' => [
                'pdf' => ['url' => '/pdfs/label_hash'],
            ],
        ];
    }
}
