<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock());

it('can add notifications', function (string $variant, $content) {
    Notifications::add('title', $content, $variant);
    Notifications::{$variant}('title', $content);

    $collection = Notifications::all();
    $items      = $collection->toArray();

    expect($items)
        ->toHaveLength(2)
        ->and($items)->each->toEqual([
            'title'    => 'title',
            'content'  => $content,
            'variant'  => $variant,
            'category' => 'api',
            'timeout'  => false,
        ]);
})
    ->with(Notification::VARIANTS)
    ->with([
        'string content' => [['content']],
        'array content'  => [[['content line 1', 'content line 2']]],
    ]);

it('can show if there are notifications', function () {
    expect(Notifications::isEmpty())
        ->toBeTrue()
        ->and(Notifications::isNotEmpty())
        ->toBeFalse();

    Notifications::add('title', 'content', Notification::VARIANT_SUCCESS);

    expect(Notifications::isEmpty())
        ->toBeFalse()
        ->and(Notifications::isNotEmpty())
        ->toBeTrue();
});
