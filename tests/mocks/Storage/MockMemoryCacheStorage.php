<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Contract\MockServiceInterface;

final class MockMemoryCacheStorage extends MemoryCacheStorage implements MockServiceInterface
{
    /**
     * @var array
     */
    private $previous = [];

    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        $this->savePreviousValue($storageKey);
        parent::delete($storageKey);
    }

    public function reset(): void
    {
        while ($item = array_shift($this->previous)) {
            $this->set($item['key'], $item['value'], true);
        }
    }

    /**
     * @param  string $storageKey
     * @param  mixed  $item
     * @param  bool   $skipSave
     *
     * @return void
     */
    public function set(string $storageKey, $item, bool $skipSave = false): void
    {
        if (! $skipSave) {
            $this->savePreviousValue($storageKey);
        }

        parent::set($storageKey, $item);
    }

    /**
     * @param  string $key
     *
     * @return void
     */
    private function savePreviousValue(string $key): void
    {
        if (! $this->has($key)) {
            return;
        }

        $this->previous[] = [
            'key'   => $key,
            'value' => $this->get($key),
        ];
    }
}
