<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface PdkMultipleRepositoryInterface extends PdkRepositoryInterface
{
    /**
     * Get multiple entities.
     */
    public function getMany($input);
}
