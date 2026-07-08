<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Exception\PdkConfigException;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk as PdkBase;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\value;

afterEach(function () {
    Pdk::setPdkInstance(null);

    PdkFactory::setCacheVersion(null);
});

it('works', function () {
    PdkFactory::create(MockPdkConfig::create());

    expect(Pdk::get(ApiServiceInterface::class))->toBeInstanceOf(
        ApiServiceInterface::class
    );
});

it('exposes mode property', function (string $mode, bool $isDevelopment) {
    PdkFactory::create(MockPdkConfig::create(['mode' => value($mode)]));

    expect(Pdk::getMode())
        ->toBe($mode)
        ->and(Pdk::isDevelopment())
        ->toBe($isDevelopment)
        ->and(Pdk::isProduction())
        ->toBe(! $isDevelopment);
})->with([
    'production'  => [
        'mode'          => PdkBase::MODE_PRODUCTION,
        'isDevelopment' => false,
    ],
    'development' => [
        'mode'          => PdkBase::MODE_DEVELOPMENT,
        'isDevelopment' => true,
    ],
]);

it('sets up cache when required', function () {
    putenv('PDK_DISABLE_CACHE=0');
    PdkFactory::create(MockPdkConfig::create(['mode' => 'production']));
    putenv('PDK_DISABLE_CACHE=1');

    expect(scandir(PdkBase::CACHE_DIR . '/default'))->toContain('CompiledContainer.php');
});

it('compiles container into a version specific cache directory', function () {
    PdkFactory::setCacheVersion('9.9.9-test');

    putenv('PDK_DISABLE_CACHE=0');
    PdkFactory::create(MockPdkConfig::create(['mode' => 'production']));
    putenv('PDK_DISABLE_CACHE=1');

    expect(scandir(PdkBase::CACHE_DIR . '/9.9.9-test'))->toContain('CompiledContainer.php');
});

it('clears cache files of all versions recursively', function () {
    PdkFactory::create(MockPdkConfig::create());

    $staleDir = PdkBase::CACHE_DIR . '/1.0.0-stale';

    if (! is_dir($staleDir)) {
        mkdir($staleDir, 0755, true);
    }

    file_put_contents($staleDir . '/CompiledContainer.php', '<?php // stale');

    Pdk::clearCache();

    expect(is_dir($staleDir))
        ->toBeFalse()
        ->and(is_dir(PdkBase::CACHE_DIR) ? array_diff(scandir(PdkBase::CACHE_DIR), ['.', '..']) : [])
        ->toBeEmpty();
});

it('falls back to the default cache directory for unsafe cache versions', function (string $version) {
    PdkFactory::setCacheVersion($version);

    putenv('PDK_DISABLE_CACHE=0');
    PdkFactory::create(MockPdkConfig::create(['mode' => 'production']));
    putenv('PDK_DISABLE_CACHE=1');

    expect(scandir(PdkBase::CACHE_DIR . '/default'))->toContain('CompiledContainer.php');
})->with(['..', '../evil', '', '.']);

it('falls back to unknown pdk version when composer.json cannot be read', function () {
    PdkFactory::create(MockPdkConfig::create(['rootDir' => value('/nonexistent/pdk-root/')]));

    expect(Pdk::get('pdkVersion'))
        ->toBe('unknown')
        ->and(Pdk::get('pdkNextMajorVersion'))
        ->toBe('unknown');
});

it('throws error if appInfo is missing', function () {
    PdkFactory::create();

    Pdk::getAppInfo();
})->throws(PdkConfigException::class);

it('throws error if appInfo is not an instance of AppInfo', function () {
    PdkFactory::create(MockPdkConfig::create(['appInfo' => value('foo')]));

    Pdk::getAppInfo();
})->throws(PdkConfigException::class);
