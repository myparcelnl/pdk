<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection,NullPointerExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockWhoamiService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

afterEach(function () {
    MockWhoamiService::reset();
});

it('stores whoami features in account subscriptionFeatures', function () {
    TestBootstrapper::hasAccount();
    MockWhoamiService::withFeatures([
        PdkAccountFeaturesService::FEATURE_ORDER_NOTES,
        PdkAccountFeaturesService::FEATURE_DIRECT_PRINTING,
        PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT,
    ]);

    Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);

    $account = AccountSettings::getAccount();

    expect($account->subscriptionFeatures->toArray())->toBe([
        PdkAccountFeaturesService::FEATURE_ORDER_NOTES,
        PdkAccountFeaturesService::FEATURE_DIRECT_PRINTING,
        PdkAccountFeaturesService::FEATURE_ORDER_MANAGEMENT,
    ]);
});

it('stores empty features when whoami returns no features', function () {
    TestBootstrapper::hasAccount();
    MockWhoamiService::withFeatures([]);

    Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);

    $account = AccountSettings::getAccount();

    expect($account->subscriptionFeatures->toArray())->toBe([]);
});
