<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService;
use MyParcelNL\Sdk\Client\Generated\IamApi\Model\FixedPrincipal;

/**
 * Test double for WhoamiService.
 *
 * Returns a synthetic FixedPrincipal whose features can be configured via
 * withFeatures(). This bypasses the IAM HTTP transport entirely so tests
 * never make real /whoami requests.
 *
 * Registered in MockPdkConfig so the DI container injects this class wherever
 * WhoamiService is type-hinted (e.g. UpdateSubscriptionFeaturesAction).
 *
 * Example:
 *   MockWhoamiService::withFeatures([
 *       PdkAccountFeaturesService::FEATURE_ORDER_NOTES,
 *   ]);
 */
class MockWhoamiService extends WhoamiService
{
    /**
     * @var string[]
     */
    private static $features = [];

    /**
     * Skip the parent constructor — we never call into the real IAM DefaultApi.
     */
    public function __construct()
    {
    }

    /**
     * Configure the features that subsequent getWhoami() calls will return.
     *
     * @param  string[] $features
     */
    public static function withFeatures(array $features): void
    {
        self::$features = $features;
    }

    /**
     * Reset to the default empty state. Call from afterEach hooks.
     */
    public static function reset(): void
    {
        self::$features = [];
    }

    public function getWhoami(): FixedPrincipal
    {
        return new FixedPrincipal(['features' => self::$features]);
    }
}
