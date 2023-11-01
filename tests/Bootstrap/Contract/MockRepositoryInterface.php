<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Contract;

use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Collection;

interface MockRepositoryInterface extends RepositoryInterface
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function all(): Collection;
}
