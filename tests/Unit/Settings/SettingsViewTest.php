<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Settings\View\AbstractView;
use MyParcelNL\Pdk\Settings\View\CarrierSettingsView;
use MyParcelNL\Pdk\Settings\View\CheckoutSettingsView;
use MyParcelNL\Pdk\Settings\View\CustomsSettingsView;
use MyParcelNL\Pdk\Settings\View\GeneralSettingsView;
use MyParcelNL\Pdk\Settings\View\LabelSettingsView;
use MyParcelNL\Pdk\Settings\View\OrderSettingsView;
use MyParcelNL\Pdk\Settings\View\ProductSettingsView;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

/**
 * Shortcut to not have to write ~260 lines for the country options.
 *
 * @param  int $key
 *
 * @return array
 */
function addCountryOptions(int $key): array
{
    $keys = [];
    $i    = 0;

    foreach (CountryService::ALL as $countryCode) {
        $keys["$key.options.$i"] = $countryCode;
        $i++;
    }

    return $keys;
}

it('gets settings view', function (string $class) {
    $viewFields = (new $class())->toArray();

    assertMatchesJsonSnapshot(json_encode($viewFields));
})->with([
    'general settings'  => [
        'class' => GeneralSettingsView::class,
    ],
    'carrier settings'  => [
        'class' => CarrierSettingsView::class,
    ],
    'order settings'    => [
        'class' => OrderSettingsView::class,
    ],
    'checkout settings' => [
        'class' => CheckoutSettingsView::class,
    ],
    'customs settings'  => [
        'class' => CustomsSettingsView::class,
    ],
    'label settings'    => [
        'class' => LabelSettingsView::class,
    ],
    'product settings'  => [
        'class' => ProductSettingsView::class,
    ],
]);

it('throws error when class is invalid', function () {
    class InvalidClassView extends AbstractView
    {
        protected function getFields(): Collection
        {
            return new Collection([['class' => 'TextInput']]);
        }
    }

    (new InvalidClassView())->toArray();
})->throws(InvalidArgumentException::class);

it('throws error when type is invalid', function () {
    class InvalidTypeView extends AbstractView
    {
        protected function getFields(): Collection
        {
            return new Collection([['type' => 'TextInput']]);
        }
    }

    (new InvalidTypeView())->toArray();
})->throws(InvalidArgumentException::class);
