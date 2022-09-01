<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;

class GetLabelsResponse extends AbstractApiResponseWithBody
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
        $this->labelLink = $parsedBody['data'][$responseKey]['url'];
    }
}
