<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

/**
 * Mock repository that always returns null for find() to simulate order not found scenarios.
 */
class MockNotFoundPdkOrderRepository extends MockPdkOrderRepository
{
    public function find($id): ?PdkOrder
    {
        return null;
    }
}
