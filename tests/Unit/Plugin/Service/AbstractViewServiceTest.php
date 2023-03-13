<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Plugin\Contract\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractViewService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRenderService;
use function DI\autowire;

uses()->group('frontend');

beforeEach(function () {
    PdkFactory::create(
        MockPdkConfig::create([
                RenderServiceInterface::class => autowire(MockRenderService::class),
                ViewServiceInterface::class   => autowire(MockAbstractViewService::class),
            ]
        )
    );
});

it('renders component on correct pages', function (callable $callback, array $views, string $page) {
    global $currentPage;
    $currentPage = $page;

    $shouldRender = in_array($page, $views, true);

    $result = $callback();

    expect($result)->toBe($shouldRender ? MockRenderService::RENDERED_CONTENT : '');
})
    ->with('components')
    ->with(array_merge(MockAbstractViewService::ALL_PDK_PAGES, ['not_a_pdk_page']));

it('throws exception when trying to render an unrecognized component', function () {
    global $currentPage;
    $currentPage = MockAbstractViewService::PAGE_ORDER_LIST;

    /** @noinspection PhpUndefinedMethodInspection */
    RenderService::renderSomething('not-a-component');
})->throws(InvalidArgumentException::class);
