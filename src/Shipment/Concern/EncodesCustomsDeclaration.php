<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Support\Str;

trait EncodesCustomsDeclaration
{
    /**
     * The API rejects customs item descriptions longer than this. Product names are often longer, so the description
     * is cut here, at the API boundary, rather than in the stored declaration.
     */
    private static int $maxCustomsItemDescriptionLength = 50;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment|\MyParcelNL\Pdk\Fulfilment\Model\Shipment $shipment
     *
     * @return null|array
     */
    private function encodeCustomsDeclaration($shipment): ?array
    {
        /** @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface $countryService */
        $countryService = Pdk::get(CountryServiceInterface::class);
        $cc             = $shipment->recipient ? $shipment->recipient->cc : null;

        if (! $cc || ! $countryService->isRow($cc)) {
            return null;
        }

        $customsDeclaration = $shipment->customsDeclaration->toArray(Arrayable::ENCODED);

        foreach ($customsDeclaration['items'] ?? [] as $index => $item) {
            if (! is_string($item['description'] ?? null)) {
                continue;
            }

            $customsDeclaration['items'][$index]['description'] = Str::limit(
                $item['description'],
                self::$maxCustomsItemDescriptionLength
            );
        }

        return $customsDeclaration;
    }
}
