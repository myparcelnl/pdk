<?php
/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

trait OffsetGetPhp8
{
    /**
     * Get the value for a given offset.
     *
     * @param  mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }
}
