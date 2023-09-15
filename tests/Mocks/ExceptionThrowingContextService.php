<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Context\Service\ContextService;
use RuntimeException;

class ExceptionThrowingContextService extends ContextService
{
    protected function resolveContext(string $contextId, array $data = []): never
    {
        throw new RuntimeException('This is an exception thrown by the test');
    }
}
