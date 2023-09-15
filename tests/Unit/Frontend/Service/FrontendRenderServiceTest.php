<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Mocks\ExceptionThrowingContextService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\get;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    TestBootstrapper::hasAccount();

    factory(Settings::class)->store();
});

it('renders component', function (callable $callback) {
    $result = $callback();

    // Replace the randomly generated id with a placeholder.
    preg_match('/id="(pdk-.+?)"/m', $result, $id);
    $replacedContent = strtr($result, [$id[1] => '__ID__']);

    // Extract the context and snapshot test it separately.
    preg_match('/data-pdk-context="(.+?)"/m', $replacedContent, $context);
    $decodedContext = htmlspecialchars_decode($context[1]);

    if ('[]' !== $decodedContext) {
        $replacedContent = strtr($replacedContent, [$context[1] => '__CONTEXT__']);
        $filteredContext = (new Collection(json_decode($decodedContext, true)))->toArrayWithoutNull();

        assertMatchesJsonSnapshot(json_encode($filteredContext));
    }

    assertMatchesHtmlSnapshot($replacedContent);
})->with('components');

it('does not throw errors', function (callable $callback) {
    $reset = mockPdkProperties([
        ContextServiceInterface::class => get(ExceptionThrowingContextService::class),
    ]);

    $callback();

    expect(true)->toBeTrue();

    $reset();
})->with('components');
