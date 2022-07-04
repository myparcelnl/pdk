<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

interface StorageInterface
{
    public function delete(string $storageKey): void;

    public function get(string $storageKey);

    public function has(string $storageKey): bool;

    public function set(string $storageKey, $item): void;
}
