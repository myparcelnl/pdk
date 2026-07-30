<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockCronService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachCron;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

// UsesMockEachCron clears the scheduled tasks between tests. Without it they accumulate and the
// assertions that index into the first task pick up work scheduled by an earlier test.
usesShared(new UsesMockPdkInstance(), new UsesMockEachCron());

function pagedMigrationService(): PagedMigrationService
{
    return Pdk::get(PagedMigrationService::class);
}

/**
 * @return array[] The scheduled tasks, in the order they were scheduled.
 */
function scheduledTasks(): array
{
    /** @var MockCronService $cronService */
    $cronService = Pdk::get(CronServiceInterface::class);

    return array_values($cronService->getScheduledTasks()->all());
}

/**
 * A fetcher that serves the given pages in order and then reports nothing left.
 */
function pagesOf(array ...$pages): callable
{
    return static function (int $page) use ($pages): array {
        return $pages[$page - 1] ?? [];
    };
}

it('schedules one task per page', function () {
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([1, 2], [3, 4], [5]));

    expect(scheduledTasks())->toHaveCount(3);
});

it('gives each task the ids of its own page', function () {
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([1, 2], [3]));

    $tasks = scheduledTasks();

    expect($tasks[0]['args'][0]['ids'])->toBe([1, 2])
        ->and($tasks[1]['args'][0]['ids'])->toBe([3]);
});

it('numbers the chunks from one so logs are readable', function () {
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([1], [2]));

    $tasks = scheduledTasks();

    expect($tasks[0]['args'][0]['chunk'])->toBe(1)
        ->and($tasks[1]['args'][0]['chunk'])->toBe(2);
});

it('schedules every task against the given action', function () {
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([1], [2]));

    foreach (scheduledTasks() as $task) {
        expect($task['callback'])->toBe('migrate_things');
    }
});

it('staggers the chunks so a large shop does not run them all at once', function () {
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([1], [2], [3]), 'ids', 100, 5);

    $tasks = scheduledTasks();

    expect($tasks[1]['timestamp'] - $tasks[0]['timestamp'])->toBe(5)
        ->and($tasks[2]['timestamp'] - $tasks[1]['timestamp'])->toBe(5);
});

it('schedules nothing when there is nothing to migrate', function () {
    $chunks = pagedMigrationService()->schedulePages('migrate_things', pagesOf());

    expect(scheduledTasks())->toBeEmpty()
        ->and($chunks)->toBe(0);
});

it('reports how many chunks it scheduled', function () {
    $chunks = pagedMigrationService()->schedulePages('migrate_things', pagesOf([1], [2]));

    expect($chunks)->toBe(2);
});

it('passes the page size to the fetcher so the caller can size its own query', function () {
    $seen = [];

    pagedMigrationService()->schedulePages(
        'migrate_things',
        static function (int $page, int $pageSize) use (&$seen): array {
            $seen[] = [$page, $pageSize];

            return 1 === $page ? [1] : [];
        },
        'ids',
        25
    );

    expect($seen)->toBe([[1, 25], [2, 25]]);
});

it('lets the caller name the ids key, so already scheduled jobs keep working', function () {
    // Migration6_5_1 scheduled its chunks with an "orderIds" key. Jobs queued on a live shop before
    // an upgrade still carry that key, so the caller has to be able to keep using it.
    pagedMigrationService()->schedulePages('migrate_things', pagesOf([7]), 'orderIds');

    $context = scheduledTasks()[0]['args'][0];

    expect($context)->toHaveKey('orderIds')
        ->and($context['orderIds'])->toBe([7])
        ->and($context)->not->toHaveKey('ids');
});
