<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

trait OffsetGetPhp7
{
    /**
     * Get the value for a given offset.
     *
     * @param  mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }
}
