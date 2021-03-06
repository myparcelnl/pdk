<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;

class GetLabelsPdfResponse extends AbstractApiResponseWithBody
{
    /**
     * @var string
     */
    private $pdf;

    /**
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getPdf(): string
    {
        return $this->pdf;
    }

    /**
     * @param  string $body
     *
     * @return void
     */
    protected function parseResponseBody(string $body): void
    {
        $this->pdf = $body;
    }
}
