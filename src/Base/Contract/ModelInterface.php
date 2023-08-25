<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface ModelInterface
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null);
}
