<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use Exception;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\SharedFactoryState;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Contracts\Service\ResetInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    use MatchesSnapshots;

    /**
     * @return class-string<ResetInterface>[]
     */
    protected function getResetServices(): array
    {
        return [
            MockCarrierSchema::class,
            MockMemoryCacheStorage::class,
            MockOrderStatusService::class,
            MockPdkActionsService::class,
            SharedFactoryState::class,
        ];
    }

    /**
     * @return void
     */
    protected function resetServices(): void
    {
        if (! Facade::getPdkInstance()) {
            return;
        }

        $services = $this->getResetServices();

        foreach ($services as $service) {
            try {
                $instance = Pdk::get($service);

                if (! $instance instanceof ResetInterface) {
                    continue;
                }

                $instance->reset();
            } catch (Exception $e) {
                // Ignore
            }
        }
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->resetServices();
    }
}
