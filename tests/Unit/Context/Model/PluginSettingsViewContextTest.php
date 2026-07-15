<?php

/** @noinspection PhpUnhandledExceptionInspection, StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Frontend\View\CarrierSettingsView;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

// A fresh PDK instance per test so notifications and container bindings do not leak between tests.
usesShared(new UsesEachMockPdkInstance());

const BROKEN_CARRIER_MESSAGE = 'Call to a member function getMin() on null';

beforeEach(function () {
    factory(Settings::class)->store();
    TestBootstrapper::hasAccount();
});

/**
 * Replace the carrier settings view with one that fails the way corrupt account data does:
 * a PHP Error (not an Exception) thrown while the view is built.
 */
function stubBrokenCarrierView(): void
{
    mockPdkProperty(CarrierSettingsView::class, new class {
        public function toArray(): array
        {
            throw new \Error(BROKEN_CARRIER_MESSAGE);
        }
    });
}

it('drops only the failing section instead of aborting the whole settings page', function () {
    stubBrokenCarrierView();

    $views = (new PluginSettingsViewContext())->toArray();

    expect($views)
        ->not->toHaveKey(CarrierSettings::ID)
        ->and($views)->toHaveKey(OrderSettings::ID)
        ->and($views)->toHaveKey(LabelSettings::ID)
        ->and($views)->toHaveKey(CustomsSettings::ID)
        ->and($views)->toHaveKey(CheckoutSettings::ID);
});

it('shows a single error notification naming the section that failed', function () {
    stubBrokenCarrierView();

    new PluginSettingsViewContext();

    $errors = array_values(array_filter(
        Notifications::all()->toArrayWithoutNull(),
        static fn (array $notification): bool => ($notification['variant'] ?? null) === Notification::VARIANT_ERROR
    ));

    expect($errors)->toHaveCount(1);
    expect(strtolower((string) ($errors[0]['title'] ?? '')))->toContain(CarrierSettings::ID);
});

it('always logs the original error when a view fails', function () {
    stubBrokenCarrierView();

    new PluginSettingsViewContext();

    $loggedOriginal = false;

    foreach (Logger::getLogs() as $log) {
        $exception = $log['context']['exception'] ?? null;

        if ($exception instanceof \Throwable && $exception->getMessage() === BROKEN_CARRIER_MESSAGE) {
            $loggedOriginal = true;
            break;
        }
    }

    expect($loggedOriginal)->toBeTrue();
});
