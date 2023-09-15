<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests;

use MyParcelNL\Pdk\Tests\Uses\BaseMock;
use MyParcelNL\Sdk\src\Support\Str;
use Pest\PendingCalls\UsesCall;
use Pest\Support\Backtrace;

/**
 * @return void
 * @internal
 */
function getCallable(array $mocks, string $name): ?callable
{
    $hooks = array_filter($mocks, static fn($mock) => method_exists($mock, $name));

    if (empty($hooks)) {
        return null;
    }

    if (Str::startsWith($name, 'after')) {
        $hooks = array_reverse($hooks);
    }

    return function () use ($name, $hooks) {
        foreach ($hooks as $hook) {
            $hook->{$name}();
        }
    };
}

/**
 * @return UsesCall
 */
function usesShared(BaseMock ...$classes): UsesCall
{
    $call = new UsesCall(Backtrace::testFile(), []);

    $beforeEach = getCallable($classes, 'beforeEach');
    $afterEach  = getCallable($classes, 'afterEach');
    $beforeAll  = getCallable($classes, 'beforeAll');
    $afterAll   = getCallable($classes, 'afterAll');

    if ($beforeAll) {
        $call->beforeAll($beforeAll);
    }

    if ($beforeEach) {
        $call->beforeEach($beforeEach);
    }

    if ($afterEach) {
        $call->afterEach($afterEach);
    }

    if ($afterAll) {
        $call->afterAll($afterAll);
    }

    return $call;
}
