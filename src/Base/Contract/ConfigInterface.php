<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface ConfigInterface
{
    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key);
}
