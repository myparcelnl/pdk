<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

const TEMP_CONFIG_FILE_PATH = __DIR__ . '/../../../config/pest-temp-config.php';

beforeEach(function () {
    PdkFactory::create(['config' => \MyParcelNL\Pdk\Base\Config::class] + MockPdkConfig::DEFAULT_CONFIG);
    file_put_contents(
        TEMP_CONFIG_FILE_PATH,
        <<<'EOF'
<?php

return [
    'value' => [
        'sub' => 2
    ]
];
EOF
    );
});

afterEach(function () {
    unlink(TEMP_CONFIG_FILE_PATH);
});

it('gets entire config file', function () {
    $value = Config::get('pest-temp-config');

    expect($value)->toBeArray();
});

it('gets key from config file', function () {
    $value = Config::get('pest-temp-config.value.sub');

    expect($value)->toEqual(2);
});

it('throws error if config file does not exist', function () {
    Config::get('randomConfigFileThatDoesNotExist.property');
})->throws(InvalidArgumentException::class);
