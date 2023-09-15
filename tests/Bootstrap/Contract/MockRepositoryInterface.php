<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap\Contract;

use MyParcelNL\Pdk\Base\Support\Collection;

interface MockRepositoryInterface
{
    public function all(): Collection;
}
