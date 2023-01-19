<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Admin\Service;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Mocks\ExceptionThrowingContextService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesHtmlSnapshot;

uses()->group('frontend');

usesShared(new UsesMockPdkInstance());

it('renders component', function (callable $callback) {
    $result = $callback();

    // Replace the randomly generated id with a placeholder.
    preg_match('/id="(pdk-.+?)"/m', $result, $matches);
    $replacedContent = strtr($result, [$matches[1] => '__ID__']);

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
