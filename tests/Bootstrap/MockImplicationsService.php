<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule\ImplicationsService;

/**
 * Test double for ImplicationsService.
 *
 * Returns a configurable carrier name from getDefaultCarrierName() without
 * making any HTTP requests. Registered in MockPdkConfig so the DI container
 * injects this wherever ImplicationsService is type-hinted.
 *
 * Configure the return value before executing the code under test:
 *   MockImplicationsService::setDefaultCarrierName('POSTNL');
 *
 * Reset between tests (done automatically via UsesSdkApiMock):
 *   MockImplicationsService::reset();
 */
class MockImplicationsService extends ImplicationsService
{
    /**
     * @var null|string
     */
    private static $defaultCarrierName = null;

    /**
     * @var int
     */
    private static $callCount = 0;

    /**
     * Skip the parent constructor — we never call into the real ShippingRuleApi.
     */
    public function __construct()
    {
    }

    /**
     * Configure the carrier name returned by subsequent getDefaultCarrierName() calls.
     *
     * @param  null|string $name  V2 carrier name (e.g. "POSTNL"), or null to simulate failure.
     */
    public static function setDefaultCarrierName(?string $name): void
    {
        self::$defaultCarrierName = $name;
    }

    /**
     * Return the number of times getDefaultCarrierName() has been called since the last reset().
     *
     * @return int
     */
    public static function getCallCount(): int
    {
        return self::$callCount;
    }

    /**
     * Reset to the default null state. Call from afterEach hooks or test teardown.
     */
    public static function reset(): void
    {
        self::$defaultCarrierName = null;
        self::$callCount          = 0;
    }

    /**
     * @param  int $shopId
     *
     * @return null|string
     */
    public function getDefaultCarrierName(int $shopId): ?string
    {
        self::$callCount++;

        return self::$defaultCarrierName;
    }
}
