<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Storage\Contract\PersistentStorageInterface;

abstract class AbstractPersistentStorage implements PersistentStorageInterface
{
    public function __construct()
    {
        register_shutdown_function([$this, 'persist']);
    }
}
