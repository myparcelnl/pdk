<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use Reflector;

abstract class RefCastingModel extends Model
{
    //    protected function getClassCastableAttributeValue(string $key, $value)
    //    {
    //        $class = $this->getCasts()[$key];
    //
    //        if ($class !== ReflectionClass::class) {
    //            return parent::getClassCastableAttributeValue($key, $value);
    //        }
    //
    //        if (is_a($value, $class)) {
    //            return $value;
    //        }
    //
    //        return new $class($value['name']);
    //    }

    /**
     * @return null|\Reflector
     */
    protected function getRefAttribute(): ?Reflector
    {
        $ref = $this->attributes['ref'];

        if ($ref instanceof Reflector) {
            return $ref;
        }

        return $ref
            ? new $this->casts['ref']($ref['name'])
            : null;
    }
}
