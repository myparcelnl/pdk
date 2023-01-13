<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Mocks\ExceptionThrowingContextService;
use function DI\autowire;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;

/**
 * Replace the randomly generated ids in the html with placeholders to support snapshot testing.
 */
function replaceIds(string $result): string
{
    preg_match('/id="(pdk-.+?)"/m', $result, $matches);
    return strtr($result, [$matches[1] => '__ID__']);
}

it('renders init script', function () {
    PdkFactory::create(MockPdkConfig::create());
    $result = RenderService::renderInitScript();
    assertMatchesHtmlSnapshot(replaceIds($result));
});

it('renders modals', function () {
    PdkFactory::create(MockPdkConfig::create());
    $result = RenderService::renderModals();
    assertMatchesHtmlSnapshot(replaceIds($result));
});

it('renders notifications', function () {
    PdkFactory::create(MockPdkConfig::create());
    $result = RenderService::renderNotifications();
    assertMatchesHtmlSnapshot(replaceIds($result));
});

it('renders order card', function () {
    PdkFactory::create(MockPdkConfig::create());
    $result = RenderService::renderOrderCard(new PdkOrder(['externalIdentifier' => 'P00924872']));
    assertMatchesHtmlSnapshot(replaceIds($result));
});

it('renders order list column', function () {
    PdkFactory::create(MockPdkConfig::create());
    $result = RenderService::renderOrderListColumn(new PdkOrder(['externalIdentifier' => 'P00924878']));
    assertMatchesHtmlSnapshot(replaceIds($result));
});

it('does not throw errors', function () {
    PdkFactory::create(
        MockPdkConfig::create([
            ContextServiceInterface::class => autowire(ExceptionThrowingContextService::class),
        ])
    );

    $order = new PdkOrder();

    RenderService::renderInitScript();
    RenderService::renderModals();
    RenderService::renderNotifications();
    RenderService::renderOrderCard($order);
    RenderService::renderOrderListColumn($order);

    expect(true)->toBeTrue();
});
