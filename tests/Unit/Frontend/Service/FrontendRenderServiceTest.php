<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Mocks\ExceptionThrowingContextService;
use function DI\autowire;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend');

it('renders component', function (callable $callback) {
    PdkFactory::create(MockPdkConfig::create());

    $result = $callback();

    // Replace the randomly generated id with a placeholder.
    preg_match('/id="(pdk-.+?)"/m', $result, $id);
    $replacedContent = strtr($result, [$id[1] => '__ID__']);

    // Extract the context and snapshot test it separately.
    preg_match('/data-pdk-context="(.+?)"/m', $replacedContent, $context);
    $decodedContext = htmlspecialchars_decode($context[1]);

    if ('[]' !== $decodedContext) {
        $replacedContent = strtr($replacedContent, [$context[1] => '__CONTEXT__']);

        assertMatchesJsonSnapshot($decodedContext);
    }

    assertMatchesHtmlSnapshot($replacedContent);
})->with('components');

it('does not throw errors', function (callable $callback) {
    PdkFactory::create(
        MockPdkConfig::create([
            ContextServiceInterface::class => autowire(ExceptionThrowingContextService::class),
        ])
    );

    $callback();

    expect(true)->toBeTrue();
})->with('components');
