<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponse;
use MyParcelNL\Sdk\src\Factory\Account\CarrierConfigurationFactory;
use MyParcelNL\Sdk\src\Model\Account\CarrierOptions;
use MyParcelNL\Sdk\src\Support\Collection;

class GetCarrierOptionsResponse extends AbstractApiResponse
{
    /**
     * @var mixed
     */
    private $options;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration[] | Collection
     */
    public function getCarrierOptions(): Collection
    {
        return $this->options;
    }

    protected function parseResponseBody(string $body): void
    {
        $options       = json_decode($body, true)['data']['carrier_options'];
        $this->options = (new Collection($options))
            ->mapInto(CarrierOptions::class);
    }
}
