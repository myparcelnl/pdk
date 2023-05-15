<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('gets settings view', function (string $class) {
    /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
    $view = Pdk::get($class);

    assertMatchesJsonSnapshot(json_encode($view->toArray()));
})->with('settingsViews');
