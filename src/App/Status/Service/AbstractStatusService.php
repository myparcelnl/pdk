<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Status\Service;

use MyParcelNL\Pdk\App\Status\Contract\StatusServiceInterface;

abstract class AbstractStatusService implements StatusServiceInterface
{
    abstract public function changeOrderStatus(array $orderIds, string $status): void;
}
