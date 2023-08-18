<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

final class MockRealConfig extends Config
{
    /**
     * @param  string $filename
     *
     * @return array
     */
    protected function parsePhp(string $filename): array
    {
        $contents = $this->fileSystem->get($filename);

        return $contents ? eval('?>' . $contents) : [];
    }
}
