<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;

trait EncodesCustomsDeclaration
{
    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment|\MyParcelNL\Pdk\Fulfilment\Model\Shipment $shipment
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeCustomsDeclaration($shipment): ?array
    {
        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);
        $cc             = $shipment->recipient->cc;

        if (! $cc || ! $countryService->isRow($cc)) {
            return null;
        }

        return $shipment->customsDeclaration->toArray(Arrayable::ENCODED);
    }
}
