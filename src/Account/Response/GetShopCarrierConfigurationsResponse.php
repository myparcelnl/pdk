<?php

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponse;
use MyParcelNL\Sdk\src\Factory\Account\CarrierConfigurationFactory;
use MyParcelNL\Sdk\src\Support\Collection;

class GetShopCarrierConfigurationsResponse extends AbstractApiResponse
{
    /**
     * @var mixed
     */
    private $configurations;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration[] | Collection
     */
    public function getCarrierConfigurations(): Collection
    {
        return $this->configurations;
    }

    protected function parseResponseBody(string $body): void
    {
        $configurations       = json_decode($body, true)['data']['carrier_configurations'];
        $this->configurations = (new Collection($configurations))->map(function (array $data) {
            return CarrierConfigurationFactory::create($data);
        });
    }
}
