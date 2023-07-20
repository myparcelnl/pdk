<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function getAllStorageKey(): string;

    /**
     * @return string
     */
    public function getKeyPrefix(): string;

    /**
     * @template T
     * @param  string           $key
     * @param  null|callable<T> $callback
     * @param  bool             $force
     * @param  null|mixed       $storage
     *
     * @return T
     */
    public function retrieve(
        string           $key,
        ?callable        $callback = null,
        bool             $force = false,
        StorageInterface $storage = null
    );

    /**
     * @template T
     * @param  string                                                 $key
     * @param  null|T                                                 $value
     * @param  null|\MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     *
     * @return T
     */
    public function store(
        string           $key,
                         $value,
        StorageInterface $storage = null
    );
}
