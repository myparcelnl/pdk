<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetLabelsPdfResponse extends ApiResponseWithBody
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

    protected function parseResponseBody(): void
    {
        $this->pdf = $this->getBody();
    }
}
