<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;

uses()->group('frontend');

beforeEach(function () {
    mockPdkProperties([
        FrontendRenderServiceInterface::class => autowire(MockFrontendRenderService::class),
        ViewServiceInterface::class           => autowire(MockAbstractViewService::class),
    ]);
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
