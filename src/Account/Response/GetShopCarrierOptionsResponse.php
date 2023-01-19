<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

class GetShopCarrierOptionsResponse extends ApiResponseWithBody
{
    /**
     * @var CarrierOptionsCollection
     */
    private $options;

    /**
     * @return CarrierOptionsCollection
     */
    public function getCarrierOptions(): CarrierOptionsCollection
    {
        return $this->options;
    }

    protected function parseResponseBody(): void
    {
        $options = json_decode($this->getBody(), true)['data']['carrier_options'];

        $this->options = new CarrierOptionsCollection(
            array_map(static function (array $option) {
                return [
                    'carrier' => [
                        'id'             => $option['carrier_id'] ?? $option['carrier']['id'] ?? null,
                        'name'           => $option['carrier']['name'] ?? null,
                        'subscriptionId' => $option['subscription_id'] ?? null,
                        'enabled'        => $option['enabled'] ?? null,
                        'label'          => $option['label'] ?? null,
                        'optional'       => $option['optional'] ?? null,
                        'primary'        => $option['primary'] ?? null,
                        'type'           => $option['type'] ?? null,
                    ],
                ];
            }, $options)
        );
    }
}
