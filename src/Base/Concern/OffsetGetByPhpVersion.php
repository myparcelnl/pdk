<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

/*
 * Ugly fix for the following error in php 7:
 *   Return type of MyParcelNL\Pdk\Base\Model\Model::offsetGet($offset) should either be compatible with
 *   ArrayAccess::offsetGet(mixed $offset): mixed, or the #[\ReturnTypeWillChange] attribute should be used to
 *   temporarily suppress the notice.
 *
 * @TODO: Remove this when we no longer have to support PHP 7.
 */
if (PHP_VERSION_ID >= 80000) {
    trait OffsetGetByPhpVersion
    {
        use OffsetGetPhp8;
    }
} else {
    trait OffsetGetByPhpVersion
    {
        use OffsetGetPhp7;
    }
}
