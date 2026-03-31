<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Service;

use MyParcelNL\Pdk\Account\Contract\AccountFeaturesServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function makeAccountWithFeatures(array $features): void
{
    TestBootstrapper::hasAccount();

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository $repo */
    $repo    = Pdk::get(MockPdkAccountRepository::class);
    $account = $repo->getAccount();

    $account->subscriptionFeatures = new \MyParcelNL\Pdk\Base\Support\Collection($features);
    $repo->store($account);
}

it('resolves as AccountFeaturesServiceInterface from the container', function () {
    $service = Pdk::get(AccountFeaturesServiceInterface::class);
    expect($service)->toBeInstanceOf(PdkAccountFeaturesService::class);
});

// canUseOrderNotes

it('returns true for canUseOrderNotes when ORDER_NOTES feature is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_ORDER_NOTES]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->canUseOrderNotes())->toBeTrue();
});

it('returns false for canUseOrderNotes when ORDER_NOTES feature is absent', function () {
    makeAccountWithFeatures([]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->canUseOrderNotes())->toBeFalse();
});

// canUseDirectPrinting

it('returns true for canUseDirectPrinting when DIRECT_PRINTING feature is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_DIRECT_PRINTING]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->canUseDirectPrinting())->toBeTrue();
});

it('returns false for canUseDirectPrinting when DIRECT_PRINTING feature is absent', function () {
    makeAccountWithFeatures([]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->canUseDirectPrinting())->toBeFalse();
});

// getOrderModeVersion — precedence & fallback

it('returns 2 when ORDER_MANAGEMENT (v2) is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->getOrderModeVersion())->toBe(2);
});

it('returns 1 when only LEGACY_ORDER_MANAGEMENT (v1) is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->getOrderModeVersion())->toBe(1);
});

it('returns 2 (v2 wins) when both ORDER_MANAGEMENT and LEGACY_ORDER_MANAGEMENT are present', function () {
    makeAccountWithFeatures([
        PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT,
        PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT,
    ]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->getOrderModeVersion())->toBe(2);
});

it('returns 0 (shipments fallback) when neither order management feature is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_ORDER_NOTES]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->getOrderModeVersion())->toBe(0);
});

it('returns 0 (shipments fallback) when account has no features at all', function () {
    makeAccountWithFeatures([]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->getOrderModeVersion())->toBe(0);
});

// usesOrderMode

it('returns true for usesOrderMode when any order management feature is present', function () {
    makeAccountWithFeatures([PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->usesOrderMode())->toBeTrue();
});

it('returns false for usesOrderMode when no order management feature is present', function () {
    makeAccountWithFeatures([]);
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->usesOrderMode())->toBeFalse();
});

// null account

it('returns false for all features when no account is set', function () {
    $service = Pdk::get(AccountFeaturesServiceInterface::class);

    expect($service->canUseOrderNotes())->toBeFalse()
        ->and($service->canUseDirectPrinting())->toBeFalse()
        ->and($service->canUseMyReturns())->toBeFalse()
        ->and($service->usesOrderMode())->toBeFalse()
        ->and($service->getOrderModeVersion())->toBe(0);
});
