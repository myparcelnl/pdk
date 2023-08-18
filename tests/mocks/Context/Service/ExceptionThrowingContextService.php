<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Service;

use RuntimeException;

final class ExceptionThrowingContextService extends ContextService
{
    /**
     * @param  string $contextId
     * @param  array  $data
     */
    protected function resolveContext(string $contextId, array $data = []): void
    {
        throw new RuntimeException('This is an exception thrown by the test');
    }
}
