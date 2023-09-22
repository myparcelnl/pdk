<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock());

it('can add notifications', function (string $variant, $content, ?string $category, array $tags) {
    Notifications::add('title', $content, $variant, $category, $tags);
    Notifications::{$variant}('title', $content, $category, $tags);

    $collection = Notifications::all();
    $items      = $collection->toArrayWithoutNull();

    expect($items)
        ->toHaveLength(2)
        ->and($items)->each->toEqual([
            'title'    => 'title',
            'content'  => Arr::wrap($content),
            'variant'  => $variant,
            'category' => $category ?? 'api',
            'tags'     => $tags,
            'timeout'  => false,
        ]);
})
    ->with(Notification::VARIANTS)
    ->with([
        'string content' => ['content'],
        'array content'  => [['content line 1', 'content line 2']],
    ])
    ->with([
        'string category' => 'api',
        'null category'   => null,
    ])
    ->with([
        'some tags' => [['action' => 'myAction', 'orderIds' => '123']],
        'no tags'   => [[]],
    ]);

it('can show if there are notifications', function () {
    expect(Notifications::isEmpty())
        ->toBeTrue()
        ->and(Notifications::isNotEmpty())
        ->toBeFalse();

    Notifications::success('title', 'content');

    expect(Notifications::isEmpty())
        ->toBeFalse()
        ->and(Notifications::isNotEmpty())
        ->toBeTrue();
});
