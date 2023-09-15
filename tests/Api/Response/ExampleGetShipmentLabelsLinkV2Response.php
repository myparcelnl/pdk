<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShipmentLabelsLinkV2Response extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'url' => '/pdfs/label_hash',
            ],
        ];
    }

    protected function getResponseProperty(): string
    {
        return 'pdf';
    }
}
