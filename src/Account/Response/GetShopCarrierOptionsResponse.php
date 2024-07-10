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

        $collection = new CarrierCollection(array_map(static function (array $option) {
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
        }, $options));

        $this->options = $this->createCarrierCollection($collection);
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection $collection
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    private function createCarrierCollection(CarrierCollection $collection): CarrierCollection
    {
        $enabledPostNlCarriers = $collection
            ->where('id', Carrier::CARRIER_POSTNL_ID)
            ->where('enabled', true);

        // If multiple PostNL carriers are enabled it means there's a custom PostNL contract enabled in the account.
        if ($enabledPostNlCarriers->count() > 1) {
            $customPostNlCarrier = $enabledPostNlCarriers->firstWhere('type', Carrier::TYPE_CUSTOM);

            // Remove all PostNL carriers except the custom one.
            return $collection->reject(static function (Carrier $carrier) use ($customPostNlCarrier) {
                return $carrier->id === Carrier::CARRIER_POSTNL_ID && $carrier->contractId !== $customPostNlCarrier->contractId;
            });
        }

        return $collection;
    }
}
