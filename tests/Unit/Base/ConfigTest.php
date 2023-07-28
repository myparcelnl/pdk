<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use Brick\VarExporter\VarExporter;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use RuntimeException;

function getFiles(): array
{
    return [
        ['tmp-config-1.php', ['value1' => ['sub1' => 1]]],
        ['tmp-config-2.inc', ['value2' => ['sub2' => 2]]],
        ['nested/tmp-config-3.php', ['value3' => ['sub3' => 3]]],
        ['nested/tmp-config-4.php', ['value4' => ['sub4' => 4]]],
    ];
}

function getFullPath(?string $path = ''): string
{
    return PdkFacade::get('rootDir') . '.tmp/tests/' . $path;
}

beforeEach(function () {
    MockPdkFactory::create();

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk        = PdkFacade::get(PdkInterface::class);
    $configDirs = array_merge($pdk->get('configDirs'), [getFullPath()]);

    $pdk->set('configDirs', $configDirs);

    foreach (getFiles() as [$path, $value]) {
        $fullPath = getFullPath($path);
        $dir      = dirname($fullPath);

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        file_put_contents($fullPath, "<?php\n\n" . VarExporter::export($value, VarExporter::ADD_RETURN));
    }
});

afterEach(function () {
    foreach (getFiles() as [$path]) {
        unlink(getFullPath($path));
    }
});

it('gets a config file', function (string $input, bool $fullPath) {
    $value = Config::get($fullPath ? getFullPath($input) : $input);

    expect($value)
        ->toBeArray()
        ->and($value)
        ->toBe([
            'value1' => [
                'sub1' => 1,
            ],
        ]);
})->with(function () {
    return [
        'key'       => ['tmp-config-1', false],
        'full path' => ['tmp-config-1.php', true],
    ];
});

it('gets a directory', function (string $input, bool $fullPath) {
    $value = Config::get($fullPath ? getFullPath($input) : $input);

    expect($value)->toBe([
        'tmp-config-3.php' => ['value3' => ['sub3' => 3]],
        'tmp-config-4.php' => ['value4' => ['sub4' => 4]],
    ]);
})->with(function () {
    return [
        'key'       => ['nested', false],
        'full path' => ['nested', true],
    ];
});

it('gets key from config file', function () {
    $value = Config::get('tmp-config-1.value1.sub1');

    expect($value)->toEqual(1);
});

it('returns null if key does not exist', function () {
    $value = Config::get('tmp-config-1.value1.nonExistingKey');

    expect($value)->toBeNull();
});

it('throws error if config file does not exist', function () {
    Config::get('randomConfigFileThatDoesNotExist.property');
})->throws(InvalidArgumentException::class);


