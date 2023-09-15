<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use Brick\VarExporter\VarExporter;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRealConfig;
use function DI\get;

function getFiles(): array
{
    return [
        ['/tmp/config/tmp-config-1.php', ['value1' => ['sub1' => 1]]],
        ['/tmp/config/tmp-config-2.inc', ['value2' => ['sub2' => 2]]],
        ['/tmp/config/nested/tmp-config-3.php', ['value3' => ['sub3' => 3]]],
        ['/tmp/config/nested/tmp-config-4.php', ['value4' => ['sub4' => 4]]],
    ];
}

beforeEach(function () {
    MockPdkFactory::create([
        ConfigInterface::class => get(MockRealConfig::class),
    ]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem $fileSystem */
    $fileSystem = PdkFacade::get(FileSystemInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $pdk */
    $pdk = PdkFacade::get(PdkInterface::class);

    $configDirs = array_merge($pdk->get('configDirs'), ['/tmp/config']);

    $pdk->set('configDirs', $configDirs);

    foreach (getFiles() as [$path, $value]) {
        $fileSystem->put($path, "<?php\n\n" . VarExporter::export($value, VarExporter::ADD_RETURN));
    }
});

it('gets a config file', function (string $input) {
    $value = Config::get($input);

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
        'key'       => ['tmp-config-1'],
        'full path' => ['/tmp/config/tmp-config-1.php'],
    ];
});

it('gets a directory', function (string $input) {
    $value = Config::get($input);

    expect($value)->toBe([
        'tmp-config-3.php' => ['value3' => ['sub3' => 3]],
        'tmp-config-4.php' => ['value4' => ['sub4' => 4]],
    ]);
})->with(function () {
    return [
        'key'       => ['nested'],
        'full path' => ['/tmp/config/nested'],
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
