<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

usesShared(new UsesMockPdkInstance());

it('gets settings view', function (string $class) {
    TestBootstrapper::hasAccount();

    /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
    $view = Pdk::get($class);

    assertMatchesJsonSnapshot(json_encode($view->toArray(Arrayable::SKIP_NULL)));
})->with('settingsViews');
