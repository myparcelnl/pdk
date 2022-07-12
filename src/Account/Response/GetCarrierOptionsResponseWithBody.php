<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Sdk\src\Model\Account\CarrierOptions;

class GetCarrierOptionsResponseWithBody extends AbstractApiResponseWithBody
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
