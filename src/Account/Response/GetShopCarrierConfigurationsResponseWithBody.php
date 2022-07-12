<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Sdk\src\Factory\Account\CarrierConfigurationFactory;

class GetShopCarrierConfigurationsResponseWithBody extends AbstractApiResponseWithBody
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
        $parsedBody           = json_decode($body, true);
        $configurations       = $parsedBody['data']['carrier_configurations'] ?? [];
        $this->configurations = (new Collection($configurations))->map(
            function (array $data) {
                return CarrierConfigurationFactory::create($data);
            }
        );
    }
}
