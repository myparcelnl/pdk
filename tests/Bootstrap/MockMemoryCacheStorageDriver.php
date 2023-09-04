<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Storage\MemoryCacheStorageDriver;
use Symfony\Contracts\Service\ResetInterface;

class MockMemoryCacheStorageDriver extends MemoryCacheStorageDriver implements ResetInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected $deletes;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected $reads;

    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected $writes;

    public function __construct()
    {
        $this->deletes = new Collection();
        $this->reads   = new Collection();
        $this->writes  = new Collection();
    }

    /**
     * Clear all data and reset reads/writes.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];

        $this->deletes = new Collection();
        $this->reads   = new Collection();
        $this->writes  = new Collection();
    }

    public function delete(string $storageKey): void
    {
        $this->deletes->push(compact('storageKey'));
        parent::delete($storageKey);
    }

    public function get(string $storageKey)
    {
        $this->reads->push(compact('storageKey'));
        return parent::get($storageKey);
    }

    public function getDeletes(): Collection
    {
        return $this->deletes;
    }

    public function getReads(): Collection
    {
        return $this->reads;
    }

    public function getWrites(): Collection
    {
        return $this->writes;
    }

    public function put(string $storageKey, $value): void
    {
        $this->writes->push(compact('storageKey', 'value'));
        parent::put($storageKey, $value);
    }

    public function reset(): void
    {
        $this->clear();
    }
}
