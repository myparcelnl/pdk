<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('creates storable array', function () {
    TestBootstrapper::hasAccount();

    $account = AccountSettings::getAccount();

    expect($account)->not->toBeNull();
    /** @noinspection NullPointerExceptionInspection */
    assertMatchesJsonSnapshot(json_encode($account->toStorableArray()));
});
