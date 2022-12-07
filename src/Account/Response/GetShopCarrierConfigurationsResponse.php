<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Sdk\src\Factory\Account\CarrierConfigurationFactory;

class GetShopCarrierConfigurationsResponse extends ApiResponseWithBody
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

    protected function parseResponseBody(): void
    {
        $parsedBody           = json_decode($this->getBody(), true);
        $configurations       = $parsedBody['data']['carrier_configurations'] ?? [];
        $this->configurations = (new Collection($configurations))->map(
            function (array $data) {
                return CarrierConfigurationFactory::create($data);
            }
        );
    }
}
