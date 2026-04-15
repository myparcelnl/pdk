<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockCarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;

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

        $repo = Pdk::get(CarrierCapabilitiesRepository::class);

        if ($repo instanceof MockCarrierCapabilitiesRepository) {
            $repo->enablePassthrough();
        }
    }

    public function afterEach(): void
    {
        MockSdkApiHandler::reset();

        $repo = Pdk::get(CarrierCapabilitiesRepository::class);

        if ($repo instanceof MockCarrierCapabilitiesRepository) {
            $repo->disablePassthrough();
        }
    }
}
