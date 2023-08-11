<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetLabelsResponse extends ApiResponseWithBody
{
    /**
     * @var string
     */
    private $labelLink;

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getLink(): string
    {
        return $this->labelLink;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody      = json_decode($this->getBody(), true);
        $responseKey     = array_key_exists('pdf', $parsedBody['data']) ? 'pdf' : 'pdfs';
        $this->labelLink = 'pdfs' === $responseKey ? $parsedBody['data'][$responseKey]['url']
            : $parsedBody['data'][$responseKey][0]['url'];
    }
}
