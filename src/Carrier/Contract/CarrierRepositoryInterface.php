<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Contract;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

interface CarrierRepositoryInterface
{
    public function all(): CarrierCollection;

    public function get(array $input): ?Carrier;
}
