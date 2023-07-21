<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

usesShared(new UsesMockPdkInstance());

it('gets settings view', function (string $class) {
    /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
    $view = Pdk::get($class);

    assertMatchesJsonSnapshot(json_encode($view->toArray()));
})->with('settingsViews');
