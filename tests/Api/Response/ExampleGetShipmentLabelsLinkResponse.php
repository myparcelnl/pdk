<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShipmentLabelsLinkResponse extends ExampleJsonResponse
{
    /**
     * @return string[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            'url' => '/pdfs/label_hash',
        ];
    }

    protected function getResponseProperty(): string
    {
        return 'pdfs';
    }
}
