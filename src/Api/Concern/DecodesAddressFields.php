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
     * @param  null|array $item
     *
     * @return null|array
     */
    protected function filter(?array $item): ?array
    {
        return array_filter($item ?? []) ?: null;
    }
}
