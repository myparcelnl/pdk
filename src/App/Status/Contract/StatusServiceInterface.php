<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Status\Contract;

interface StatusServiceInterface
{
    /**
     * @param  array  $orderIds
     * @param  string $status
     *
     * @return void
     */
    public function changeOrderStatus(array $orderIds, string $status): void;
}
