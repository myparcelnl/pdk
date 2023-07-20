<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

/**
 * This interface is used for caching. This can be the memory cache (default), or any other cache storage.
 *
 * @see \MyParcelNL\Pdk\Storage\MemoryCacheStorage
 */
interface CacheStorageInterface extends StorageInterface
{
}
