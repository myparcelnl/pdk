<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Concern;

use MyParcelNL\Pdk\Base\Support\Utils;

trait DecodesAddressFields
{
    /**
     * @param  null|array $input
     *
     * @return null|array
     */
    protected function decodeAddress(?array $input): ?array
    {
        $data = Utils::changeArrayKeysCase($this->filter($input) ?? []);

        if (isset($data['street']) || isset($data['number'])) {
            $data['address1'] = trim(
                implode(' ', [
                    $data['street'],
                    $data['number'],
                    $data['numberSuffix'] ?? $data['boxNumber'] ?? '',
                ])
            );

            unset($data['street'], $data['number'], $data['numberSuffix'], $data['boxNumber']);
        }

        return $data;
    }

    /**
     * @param  null|array $array
     *
     * @return array
     * @see \MyParcelNL\Pdk\App\Order\Model\ShippingAddress
     */
    protected function decodeAddress2(?array $array): array
    {
        return $this->filter([
            'eoriNumber' => $array['eori_number'] ?? null,
            'vatNumber'  => $array['vat_number'] ?? null,
            'company'    => $array['company'] ?? null,
            'email'      => $array['email'] ?? null,
            'person'     => $array['person'] ?? null,
            'phone'      => $array['phone'] ?? null,
            'address1'   => trim(implode(' ', [
                $array['street'] ?? '',
                $array['number'] ?? '',
                $array['number_suffix'] ?? $array['box_number'] ?? '',
            ])),
            'address2'   => $array['street_additional_info'] ?? null,
            'area'       => $array['area'] ?? null,
            'cc'         => $array['cc'] ?? null,
            'city'       => $array['city'] ?? null,
            'postalCode' => $array['postal_code'] ?? null,
            'region'     => $array['region'] ?? null,
            'state'      => $array['state'] ?? null,
        ]);
    }

    /**
     * @param  null|array $array
     *
     * @return array
     */
    protected function decodePickupLocationAddress(?array $array): array
    {
        return $this->filter([
            'cc'              => $array['cc'] ?? null,
            'city'            => $array['city'] ?? null,
            'locationCode'    => $array['location_code'] ?? null,
            'locationName'    => $array['location_name'] ?? null,
            'number'          => $array['number'] ?? null,
            'numberSuffix'    => $array['number_suffix'] ?? null,
            'postalCode'      => $array['postal_code'] ?? null,
            'retailNetworkId' => $array['retail_network_id'] ?? null,
            'street'          => $array['street'] ?? null,
        ]);
    }

    /**
     * @param  null|array $item
     *
     * @return null|array
     */
    protected function filter(?array $item): ?array
    {
        return array_filter($item ?? []) ?: null;
    }
}
