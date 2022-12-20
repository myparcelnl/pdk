<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesSnapshot;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

/**
 * Replace the randomly generated ids in the html with placeholders to support snapshot testing.
 */
function replaceIds(string $result): string
{
    preg_match('/id="(pdk-.+?)"/m', $result, $matches);
    return strtr($result, [$matches[1] => '__ID__']);
}

it('renders init script', function () {
    $result = RenderService::renderInitScript();
    assertMatchesSnapshot(replaceIds($result));
});

it('renders modals', function () {
    $result = RenderService::renderModals();
    assertMatchesSnapshot(replaceIds($result));
});

it('renders notifications', function () {
    $result = RenderService::renderNotifications();
    assertMatchesSnapshot(replaceIds($result));
});

it('renders order card', function () {
    $result = RenderService::renderOrderCard(new PdkOrder(['externalIdentifier' => 'P00924872']));
    assertMatchesSnapshot(replaceIds($result));
});

it('renders order list column', function () {
    $result = RenderService::renderOrderListColumn(new PdkOrder(['externalIdentifier' => 'P00924878']));
    assertMatchesSnapshot(replaceIds($result));
});
