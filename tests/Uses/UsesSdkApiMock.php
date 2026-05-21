<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockCarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockImplicationsService;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Pest hook that resets the SdkApi MockHandler around each test and enables
 * passthrough on MockCarrierCapabilitiesRepository so enqueued responses are used.
 *
 * Add to usesShared() in any test file that exercises code going through
 * a SdkApi service (e.g. CapabilitiesService via CarrierCapabilitiesRepository):
 *
 *   usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesSdkApiMock());
 */
class UsesSdkApiMock implements BaseMock
{
    public function beforeEach(): void
    {
        MockSdkApiHandler::reset();
        MockImplicationsService::reset();
        $this->resetStorage();

        $repo = Pdk::get(CarrierCapabilitiesRepository::class);

        if ($repo instanceof MockCarrierCapabilitiesRepository) {
            $repo->enablePassthrough();
        }
    }

    public function afterEach(): void
    {
        MockSdkApiHandler::reset();
        MockImplicationsService::reset();
        $this->resetStorage();

        $repo = Pdk::get(CarrierCapabilitiesRepository::class);

        if ($repo instanceof MockCarrierCapabilitiesRepository) {
            $repo->disablePassthrough();
        }
    }

    /**
     * Clear cached capabilities/contract-definitions between tests so newly enqueued
     * MockSdkApiHandler responses are actually used (the PDK instance is shared per file).
     */
    private function resetStorage(): void
    {
        $storage = Pdk::get(StorageInterface::class);

        if ($storage instanceof ResetInterface) {
            $storage->reset();
        }
    }
}
