<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

test('creates paths', function ($trailing) {
    $appInfo = new AppInfo([
        'path' => "/some/path$trailing",
    ]);

    expect($appInfo->createPath('my_file.txt'))
        ->toBe('/some/path/my_file.txt')
        ->and($appInfo->createPath('/with/slashes/vroom.txt'))
        ->toBe('/some/path/with/slashes/vroom.txt')
        ->and($appInfo->createPath('/with//some//more////slashes//yikes.txt'))
        ->toBe('/some/path/with/some/more/slashes/yikes.txt');
})->with([
    'path without trailing slash'         => [''],
    'path with trailing slash'            => ['/'],
    'path with a lot of trailing slashes' => ['////'],
]);
