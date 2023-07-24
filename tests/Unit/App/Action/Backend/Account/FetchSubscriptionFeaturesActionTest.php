<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection,NullPointerExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Account;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleAclResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('updates subscription features in account', function () {
    TestBootstrapper::hasAccount();
    MockApi::enqueue(new ExampleAclResponse());

    Actions::execute(PdkBackendActions::UPDATE_SUBSCRIPTION_FEATURES);

    $account = AccountSettings::getAccount();

    expect($account->subscriptionFeatures)->toHaveLength(3);
});

