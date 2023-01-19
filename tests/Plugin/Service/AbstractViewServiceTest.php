<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Service;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
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

$pages = array_merge(['not-a-pdk-page'], MockAbstractViewService::ALL_PDK_PAGES);

it('renders component on correct pages', function (callable $callback, array $views, string $page) {
    global $currentPage;
    $currentPage = $page;

    $shouldRender = in_array($page, $views, true);

    $result = $callback();

    expect($result)->toBe($shouldRender ? MockRenderService::RENDERED_CONTENT : '');
})
    ->with('components')
    ->with(array_combine($pages, $pages));
