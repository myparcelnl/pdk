<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;

final class UsesMockEachCron implements BaseMock
{
    public function afterEach(): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCronService $cronService */
        $cronService = Pdk::get(CronServiceInterface::class);

        $cronService->clearScheduledTasks();
    }
}
