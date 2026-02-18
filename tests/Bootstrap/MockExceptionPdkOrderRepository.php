<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use Exception;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

/**
 * Mock repository that throws an exception on find() to simulate database errors.
 */
class MockExceptionPdkOrderRepository extends MockPdkOrderRepository
{
    public function find($id): ?PdkOrder
    {
        throw new Exception('Something went wrong.');
    }
}
