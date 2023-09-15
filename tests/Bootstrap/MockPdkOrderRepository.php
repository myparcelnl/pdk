<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;

class MockPdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @param  int|string $input
     */
    public function get($input): PdkOrder
    {
        $orderData = is_array($input) ? $input : ['externalIdentifier' => $input];

        return $this->retrieve((string) $orderData['externalIdentifier'], fn() => new PdkOrder($orderData));
    }

    protected function getKeyPrefix(): string
    {
        return static::class;
    }
}
