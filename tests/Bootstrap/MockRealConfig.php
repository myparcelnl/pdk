<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Config;

final class MockRealConfig extends Config
{
    protected function parsePhp(string $filename): array
    {
        $contents = $this->fileSystem->get($filename);

        return $contents ? eval('?>' . $contents) : [];
    }
}
