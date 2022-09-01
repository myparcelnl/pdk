<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

class GetCarrierOptionsResponseWithBody extends AbstractApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection
     */
    private $options;

    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection
     */
    public function getCarrierOptions(): CarrierOptionsCollection
    {
        return $this->options;
    }

    protected function parseResponseBody(): void
    {
        $options       = json_decode($this->getBody(), true)['data']['carrier_options'];
        $this->options = new CarrierOptionsCollection($options);
    }
}
