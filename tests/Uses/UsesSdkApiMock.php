<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;

/**
 * Pest hook that resets the SdkApi MockHandler around each test.
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
    }

    public function afterEach(): void
    {
        MockSdkApiHandler::reset();
    }
}
