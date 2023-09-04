<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use Symfony\Contracts\Service\ResetInterface;

final class MockCarrierSchema extends CarrierSchema implements ResetInterface
{
    public function reset(): void
    {
        $this->cache = [];
    }
}
