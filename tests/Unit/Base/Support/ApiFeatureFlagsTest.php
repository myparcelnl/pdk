<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('turns the configured flags into headers', function () {
    mockPdkProperty('apiFeatureFlags', ['x-dmp-no-tracking']);

    expect(ApiFeatureFlags::getHeaders())->toBe(['x-dmp-no-tracking' => 'true']);
});

it('sends every configured flag', function () {
    mockPdkProperty('apiFeatureFlags', ['x-dmp-no-tracking', 'x-dmp-other-behaviour']);

    expect(ApiFeatureFlags::getHeaders())->toBe([
        'x-dmp-no-tracking'     => 'true',
        'x-dmp-other-behaviour' => 'true',
    ]);
});

it('returns no headers when no flags are configured', function () {
    mockPdkProperty('apiFeatureFlags', []);

    expect(ApiFeatureFlags::getHeaders())->toBe([]);
});

it('returns no headers when the config entry is missing', function () {
    mockPdkProperty('apiFeatureFlags', null);

    expect(ApiFeatureFlags::getHeaders())->toBe([]);
});
