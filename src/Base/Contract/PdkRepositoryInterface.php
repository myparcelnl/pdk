<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface PdkRepositoryInterface
{
    /**
     * Get a single entity.
     */
    public function get($input);
}

