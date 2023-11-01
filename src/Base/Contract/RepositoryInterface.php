<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface RepositoryInterface
{
    /**
     * @param  string        $key
     * @param  null|callable $callback
     * @param  bool          $force
     *
     * @return mixed
     */
    public function retrieve(string $key, ?callable $callback = null, bool $force = false);

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    public function save(string $key, $data);
}
