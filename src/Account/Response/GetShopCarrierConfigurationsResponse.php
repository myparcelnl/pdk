<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

class GetShopCarrierConfigurationsResponse extends ApiResponseWithBody
{
    /**
     * @var mixed
     */
    private $configurations;

    /**
     * @return \MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection
     */
    public function getCarrierConfigurations(): ShopCarrierConfigurationCollection
    {
        return $this->configurations;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody     = json_decode($this->getBody(), true);
        $configurations = $parsedBody['data']['carrier_configurations'] ?? [];

        $this->configurations = (new ShopCarrierConfigurationCollection(
            array_map(static function (array $configuration) {
                $carrier = new Carrier(['id' => $configuration['carrier_id']]);

                return ($configuration['configuration'] ?? []) + [
                        'carrier' => $carrier->externalIdentifier,
                    ];
            }, $configurations)
        ));
    }
}
