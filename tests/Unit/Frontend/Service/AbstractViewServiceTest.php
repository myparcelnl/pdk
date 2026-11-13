<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractViewService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockFrontendRenderService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\get;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        FrontendRenderServiceInterface::class => get(MockFrontendRenderService::class),
        ViewServiceInterface::class           => get(MockAbstractViewService::class),
    ])
);

beforeEach(function () {
    TestBootstrapper::hasAccount();
});

it('renders component on correct pages', function (callable $callback, array $views, string $page) {
    global $currentPage;
    $currentPage = $page;

    $shouldRender = in_array($page, $views, true);

    $result = $callback();

    expect($result)->toBe($shouldRender ? MockFrontendRenderService::RENDERED_CONTENT : '');
})
    ->with('components')
    ->with(array_merge(MockAbstractViewService::ALL_PDK_PAGES, ['not_a_pdk_page']));

it('throws exception when trying to render an unrecognized component', function () {
    global $currentPage;
    $currentPage = MockAbstractViewService::PAGE_ORDER_LIST;

    /** @noinspection PhpUndefinedMethodInspection */
    Frontend::renderSomething('not-a-component');
})->throws(InvalidArgumentException::class);

it('does not treat any page as the plugins overview by default', function () {
    // A plugin that does not implement the plugins overview yet.
    $service = new class extends AbstractViewService {
        public function isCheckoutPage(): bool
        {
            return false;
        }

        public function isChildProductPage(): bool
        {
            return false;
        }

        public function isOrderListPage(): bool
        {
            return false;
        }

        public function isOrderPage(): bool
        {
            return false;
        }

        public function isPluginSettingsPage(): bool
        {
            return false;
        }

        public function isProductPage(): bool
        {
            return false;
        }
    };

    expect($service->isPluginsOverviewPage())
        ->toBeFalse()
        ->and($service->isAnyPdkPage())
        ->toBeFalse();
});

it('does not render notifications in the plugins overview', function () {
    global $currentPage;
    $currentPage = MockAbstractViewService::PAGE_PLUGINS_OVERVIEW;

    /** @var \MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface $service */
    $service = Pdk::get(ViewServiceInterface::class);

    expect($service->isAnyPdkPage())
        ->toBeTrue()
        ->and($service->hasNotifications())
        ->toBeFalse();
});
