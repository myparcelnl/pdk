<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Proposition\Model\PropositionAvailableContract;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Carrier[] $items
 */
class PropositionAvailableContractsCollection extends Collection
{
    protected $cast = PropositionAvailableContract::class;
}
