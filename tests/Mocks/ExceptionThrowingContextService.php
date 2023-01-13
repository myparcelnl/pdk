<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Plugin\Service\ContextService;
use RuntimeException;

class ExceptionThrowingContextService extends ContextService
{
    /**
     * @param  string $contextId
     * @param  array  $data
     */
    protected function resolveContext(string $contextId, array $data = [])
    {
        throw new RuntimeException('This is an exception thrown by the test');
    }
}
