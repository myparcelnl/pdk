<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Notification\Model\NotificationTags;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock());

it('can add notifications', function (string $variant, $content, ?string $category, ?NotificationTags $tags) {
    Notifications::add('title', $content, $variant, $category, $tags);
    Notifications::{$variant}('title', $content, $category, $tags);

    $collection = Notifications::all();
    $items      = $collection->toArray();

    expect($items)
        ->toHaveLength(2)
        ->and($items)->each->toEqual([
            'title'    => 'title',
            'content'  => $content,
            'variant'  => $variant,
            'category' => $category ?? 'api',
            'tags'     => null === $tags ? null : $tags->toArray(),
            'timeout'  => false,
        ]);
})
    ->with(Notification::VARIANTS)
    ->with([
        'string content' => [['content']],
        'array content'  => [[['content line 1', 'content line 2']]],
    ])
    ->with([
        'string category' => 'api',
        'null category'   => null,
    ])
    ->with([
        'some tags' => new NotificationTags(['action' => 'myAction', 'orderIds' => '123']),
        'no tags'   => null,
    ]);

it('can show if there are notifications', function () {
    expect(Notifications::isEmpty())
        ->toBeTrue()
        ->and(Notifications::isNotEmpty())
        ->toBeFalse();

    Notifications::add('title', 'content', Notification::VARIANT_SUCCESS, null, null);

    expect(Notifications::isEmpty())
        ->toBeFalse()
        ->and(Notifications::isNotEmpty())
        ->toBeTrue();
});
