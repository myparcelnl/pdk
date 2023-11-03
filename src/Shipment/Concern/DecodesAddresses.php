<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Support\Arr;

trait DecodesAddresses
{
    protected function decodeAddress(array $address): array
    {
        return [
            'address1'    => trim(implode(' ', array_filter([
                Arr::get($address, 'street'),
                Arr::get($address, 'number'),
                Arr::get($address, 'number_suffix'),
            ]))),
            'address2'    => Arr::get($address, 'street_additional_info'),
            'area'        => Arr::get($address, 'area'),
            'cc'          => Arr::get($address, 'cc'),
            'city'        => Arr::get($address, 'city'),
            'postal_code' => Arr::get($address, 'postal_code'),
            'region'      => Arr::get($address, 'region'),
            'state'       => Arr::get($address, 'state'),
        ];
    }

    protected function decodeRetailLocation(array $input): array
    {
        return [
            'locationCode'    => Arr::get($input, 'location_code'),
            'locationName'    => Arr::get($input, 'location_name'),
            'retailNetworkId' => Arr::get($input, 'retail_network_id'),
            'boxNumber'       => Arr::get($input, 'box_number'),
            'cc'              => Arr::get($input, 'cc'),
            'city'            => Arr::get($input, 'city'),
            'number'          => Arr::get($input, 'number'),
            'numberSuffix'    => Arr::get($input, 'number_suffix'),
            'postalCode'      => Arr::get($input, 'postal_code'),
            'region'          => Arr::get($input, 'region'),
            'state'           => Arr::get($input, 'state'),
            'street'          => Arr::get($input, 'street'),
        ];
    }
}
