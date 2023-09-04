<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

use MyParcelNL\Pdk\Base\Support\Collection;

interface StorageStackInterface extends StorageDriverInterface
{
    public function getLayers(): Collection;
}
