<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Utils;

final class MockArrayable implements Arrayable
{
    /**
     * @var array
     */
    private $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function toArray(?int $flags = null): array
    {
        if ($flags & Arrayable::SKIP_NULL) {
            return Utils::filterNull($this->attributes);
        }

        return $this->attributes;
    }
}
