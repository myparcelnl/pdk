<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Contract;

use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

interface CarrierRepositoryInterface extends RepositoryInterface
{
    /**
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function all(): CarrierCollection;

    /**
     * @param  array $input
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function get(array $input): ?Carrier;
}
