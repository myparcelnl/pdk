<?php

declare(strict_types=1);

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;

trait EncodesAddresses
{
    /**
     * @param  \MyParcelNL\Pdk\Base\Model\Model $address
     *
     * @return array
     */
    protected function encodeAddress(Model $address): array
    {
        if ($address instanceof RetailLocation) {
            return $this->encodeRetailLocation($address);
        }

        if ($address instanceof ShippingAddress) {
            return $this->encodeShippingAddress($address);
        }

        if ($address instanceof ContactDetails) {
            return $this->encodeContactDetails($address);
        }

        if ($address instanceof Address) {
            return $this->encodePlainAddress($address);
        }

        return [];
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\ContactDetails $contactDetails
     *
     * @return array
     */
    private function encodeContactDetails(ContactDetails $contactDetails): array
    {
        return array_replace($this->encodePlainAddress($contactDetails), Utils::filterNull([
            'company' => $contactDetails->company,
            'email'   => $contactDetails->email,
            'person'  => $contactDetails->person,
            'phone'   => $contactDetails->phone,
        ]));
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\Address $address
     *
     * @return array
     */
    private function encodePlainAddress(Address $address): array
    {
        $street = trim(implode(' ', [$address->address1, $address->address2])) ?: null;

        return Utils::filterNull([
            'area'                   => $address->area,
            'cc'                     => $address->cc,
            'city'                   => $address->city,
            'postal_code'            => $address->postalCode,
            'region'                 => $address->region,
            'state'                  => $address->state,
            'street'                 => $street,
            'street_additional_info' => $address->address2,
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\RetailLocation $retailLocation
     *
     * @return array
     */
    private function encodeRetailLocation(RetailLocation $retailLocation): array
    {
        return Utils::filterNull([
            'box_number'        => $retailLocation->boxNumber,
            'cc'                => $retailLocation->cc,
            'city'              => $retailLocation->city ?? '',
            'location_code'     => $retailLocation->locationCode,
            'location_name'     => $retailLocation->locationName ?? '',
            'number'            => $retailLocation->number ?? '',
            'number_suffix'     => $retailLocation->numberSuffix,
            'postal_code'       => $retailLocation->postalCode ?? '',
            'region'            => $retailLocation->region,
            'retail_network_id' => $retailLocation->retailNetworkId,
            'state'             => $retailLocation->state,
            'street'            => $retailLocation->street ?? '',
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\ShippingAddress $shippingAddress
     *
     * @return array
     */
    private function encodeShippingAddress(ShippingAddress $shippingAddress): array
    {
        return array_replace($this->encodeContactDetails($shippingAddress), Utils::filterNull([
            'eori_number' => $shippingAddress->eoriNumber,
            'vat_number'  => $shippingAddress->vatNumber,
        ]));
    }
}
