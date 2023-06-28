<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Contract;

use MyParcelNL\Pdk\Base\Support\Collection;

interface CarrierRepositoryInterface
{
    /**
     * @return array[]|Collection
     */
    public function all(): Collection;

    /**
     * @param  array $input
     *
     * @return null|array
     */
    public function get(array $input): ?array;
}
