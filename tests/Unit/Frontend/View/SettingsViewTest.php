<?php
/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePrinterGroupIdResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

usesShared(new UsesEachMockPdkInstance());

it('gets settings view', function (string $class) {
    TestBootstrapper::hasAccount();
    TestBootstrapper::hasShippingMethods();

    /** @var \MyParcelNL\Pdk\Frontend\View\AbstractSettingsView $view */
    $view = Pdk::get($class);

    assertMatchesJsonSnapshot(json_encode($view->toArray(Arrayable::SKIP_NULL)));
})->with('settingsViews');

it('loads and shows printer groups', function (?array $groups, $bah = false) {
    MockApi::enqueue(new ExamplePrinterGroupIdResponse($groups));

    $view = Pdk::get(PrinterGroupIdView::class);

    assertMatchesJsonSnapshot(json_encode($view->toArray(Arrayable::SKIP_NULL)));
    MockApi::ensureLastRequest();
})->with([
    'broken group' => [
        'groups' => [
            [
                'id'   => '55b53b20-91aa-4a53-8bb2-c4c120df9921',
                'nope' => 'Test name',
            ],
        ],
    ],
    'multiple groups' => [
        'groups' => [
            [
                'id'   => '55b53b20-91aa-4a53-8bb2-c4c120df9921',
                'name' => 'Test name',
            ],
            [
                'id'   => 'd72fd4bf-7d5a-4c25-bffc-140c4c817260',
                'name' => 'Another name',
            ],
        ],
        'bah'=> true,
    ],
    'no group'  => [
        'groups' => null,
    ],
    'one group' => [
        'groups' => [
            [
                'id'   => '55b53b20-91aa-4a53-8bb2-c4c120df9921',
                'name' => 'Test name',
            ],
        ],
    ],
]);
