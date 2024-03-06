<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

class GetShopCarrierOptionsResponse extends ApiResponseWithBody
{
    /**
     * @var CarrierCollection
     */
    private $options;

    /**
     * @return CarrierCollection
     */
    public function getCarrierOptions(): CarrierCollection
    {
        return $this->options;
    }

    /**
     * The "name" property is omitted intentionally so the carrier data is filled using the CarrierRepository.
     *
     * @return void
     * @see \MyParcelNL\Pdk\Carrier\Model\Carrier::__construct()
     */
    protected function parseResponseBody(): void
    {
        $options = json_decode($this->getBody(), true)['data']['carrier_options'] ?? [];

        $this->options = new CarrierCollection(
            array_map(static function (array $option) {
                $contractId = Carrier::TYPE_CUSTOM === $option['type'] ? $option['id'] ?? null : null;

                return [
                    'id'         => $option['carrier_id'] ?? $option['carrier']['id'] ?? null,
                    'contractId' => $contractId,
                    'enabled'    => $option['enabled'] ?? null,
                    'label'      => $option['label'] ?? null,
                    'optional'   => $option['optional'] ?? null,
                    'primary'    => $option['primary'] ?? null,
                    'type'       => $option['type'] ?? null,
                ];
            }, $options)
        );
    }
}
